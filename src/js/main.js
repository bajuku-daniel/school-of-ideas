import '../scss/main.scss';

// Strip <style>...</style> blocks from inlined SVGs — each source SVG
// carries its own `.cls-1`, `.cls-2`, … rules, and once injected they
// apply *globally* to every element on the page with the same class
// (icon-03 was painting the wordmark with a blue stroke).
const stripSvgStyle = (markup) => markup.replace(/<style[\s\S]*?<\/style>/gi, '');

const starSlot = document.querySelector('[data-star]');
if (starSlot) {
  fetch('/icons/shooting-star.svg')
    .then((res) => (res.ok ? res.text() : ''))
    .then((markup) => {
      if (!markup) return;
      starSlot.innerHTML = stripSvgStyle(markup);
      // give the star a white background as in the reference
      const poly = starSlot.querySelector('polygon');
      if (poly) poly.setAttribute('fill', '#fff');
    })
    .catch(() => {});
}

/* ============================================================
   1aa) Inline-SVG icons (preserves multi-fill icons like `glasses`)

        For any `<img src="/icons/*.svg">` inside an element with
        the `data-inline-svg` attribute (or `.manifest__icon`),
        fetch the SVG and replace the <img> with the actual <svg>.

        We KEEP the SVG's internal <style> block (otherwise icons
        with mixed fills / strokes — like `glasses` (filled eyes
        inside stroked lenses) — lose half their styling).  To
        avoid the cls-1/cls-2 names colliding across SVGs, every
        class is namespaced with a per-SVG prefix.

        Two design tokens are also rewritten in the style block:
          • `#0a3aea`            → `currentColor`
          • every `stroke-width` → `var(--icon-stroke, <n>)`

        That makes both colour and stroke weight tunable from CSS
        without touching the source SVGs.
   ============================================================ */
const inlineSvgCache = new Map();
let inlineSvgCount = 0;

const fetchInlineSvg = async (src) => {
  if (inlineSvgCache.has(src)) return inlineSvgCache.get(src);

  const promise = fetch(src)
    .then((res) => (res.ok ? res.text() : ''))
    .then((raw) => {
      if (!raw) return null;
      const id = `is${++inlineSvgCount}`;

      // Namespace `.cls-N` (rule + class attribute) to avoid global bleed
      const scoped = raw
        .replace(/cls-(\d+)/g, `${id}-cls-$1`)
        // Stroke colour → currentColor (inherits from CSS `color`)
        .replace(/#0a3aea/gi, 'currentColor')
        // Stroke width in <style> blocks → one CSS variable with the original as fallback
        .replace(/stroke-width:\s*(\d+(?:\.\d+)?)px/gi,
                 'stroke-width: var(--icon-stroke, $1px)')
        // Stroke width as SVG attributes → same variable, so uploaded SVGs behave alike
        .replace(/stroke-width="(\d+(?:\.\d+)?)(?:px)?"/gi,
                 'stroke-width="var(--icon-stroke, $1px)"');

      const tmp = document.createElement('div');
      tmp.innerHTML = scoped.trim();
      return tmp.querySelector('svg');
    })
    .catch(() => null);

  inlineSvgCache.set(src, promise);
  return promise;
};

const inlineSvgHosts = document.querySelectorAll(
  '[data-inline-svg], .manifest__icon'
);
inlineSvgHosts.forEach((host) => {
  host.querySelectorAll('img').forEach(async (img) => {
    const src = img.currentSrc || img.getAttribute('src') || '';
    if (!src.toLowerCase().split('?')[0].endsWith('.svg')) return;

    const template = await fetchInlineSvg(src);
    if (!template || !img.parentNode) return;
    const svg = template.cloneNode(true);
    svg.setAttribute('aria-hidden', 'true');
    svg.removeAttribute('id');
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    img.replaceWith(svg);
  });
});

/* ============================================================
   Hero video: iOS needs a very explicit muted inline play setup.
   The template also serves a smaller mobile source before the CMS video.
   ============================================================ */
const heroVideo = document.querySelector('.hero__video');
if (heroVideo) {
  const heroVideoQuery = window.matchMedia('(max-width: 768px)');

  heroVideo.muted = true;
  heroVideo.defaultMuted = true;
  heroVideo.playsInline = true;
  heroVideo.setAttribute('muted', '');
  heroVideo.setAttribute('playsinline', '');
  heroVideo.setAttribute('webkit-playsinline', '');

  const tryPlayHero = () => {
    const promise = heroVideo.play();
    if (promise && typeof promise.catch === 'function') {
      promise.catch(() => {});
    }
  };

  const normalizeVideoUrl = (url) => {
    if (!url) return '';
    try {
      return new URL(url, window.location.href).href;
    } catch {
      return url;
    }
  };

  const syncHeroSource = () => {
    const mobileSrc = heroVideo.dataset.mobileSrc || '';
    const desktopSrc = heroVideo.dataset.desktopSrc || '';
    const nextSrc = heroVideoQuery.matches && mobileSrc ? mobileSrc : (desktopSrc || mobileSrc);
    if (!nextSrc) return;

    const normalizedNext = normalizeVideoUrl(nextSrc);
    const current = normalizeVideoUrl(heroVideo.currentSrc || heroVideo.getAttribute('src'));
    if (current === normalizedNext) return;

    heroVideo.src = nextSrc;
    heroVideo.load();
    tryPlayHero();
  };

  syncHeroSource();
  if (typeof heroVideoQuery.addEventListener === 'function') {
    heroVideoQuery.addEventListener('change', syncHeroSource);
  } else if (typeof heroVideoQuery.addListener === 'function') {
    heroVideoQuery.addListener(syncHeroSource);
  }

  if (heroVideo.readyState >= 2) {
    tryPlayHero();
  } else {
    heroVideo.addEventListener('loadedmetadata', tryPlayHero, { once: true });
  }
}

/* ============================================================
   Agency directory filters
   ============================================================ */
document.querySelectorAll('[data-agency-directory]').forEach((directory) => {
  const filters = Array.from(directory.querySelectorAll('[data-agency-filter]'));
  const cards = Array.from(directory.querySelectorAll('.agency-card'));
  const empty = directory.querySelector('[data-agency-empty]');

  const applyFilters = () => {
    const active = Object.fromEntries(
      filters.map((filter) => [filter.dataset.agencyFilter, filter.value])
    );

    let visible = 0;
    cards.forEach((card) => {
      const show = Object.entries(active).every(([key, value]) => (
        !value || card.dataset[key] === value
      ));
      card.hidden = !show;
      if (show) visible += 1;
    });

    if (empty) empty.hidden = visible !== 0;
  };

  filters.forEach((filter) => filter.addEventListener('change', applyFilters));
  applyFilters();
});

/* ============================================================
   Performance helper — rAF-throttle für Scroll-Handler.

   Scroll-Events feuern auf iOS / Firefox sehr häufig und können den
   Main-Thread verstopfen, wenn der Handler `getBoundingClientRect()`
   aufruft (was Layout zwingt).  Wir koppeln alle Scroll-Updates an
   `requestAnimationFrame`, damit pro Frame maximal EIN Update läuft —
   synchron zum Browser-Repaint, keine doppelten Reads, kein Jank.
   ============================================================ */
const rafThrottle = (fn) => {
  let pending = false;
  return (...args) => {
    if (pending) return;
    pending = true;
    requestAnimationFrame(() => {
      fn(...args);
      pending = false;
    });
  };
};

/* ============================================================
   1) Navigation: switch to glass mode once we leave the hero
   ============================================================ */
const nav  = document.getElementById('siteNav');
const hero = document.getElementById('hero');

if (nav && hero) {
  const heroObserver = new IntersectionObserver(
    ([entry]) => {
      nav.classList.toggle('is-glass', !entry.isIntersecting);
    },
    { rootMargin: '0px 0px -100% 0px', threshold: 0 }
  );
  heroObserver.observe(hero);
}

/* ============================================================
   1b) Logo scroll choreography
       Cloud stays in place inside the wordmark gap.  As the user
       scrolls down, the PNG wordmark fades out and the cloud
       grows.  Cloud transform composes its CSS centring + scale
       via the `--cloud-scale` variable.
   ============================================================ */
const brandText = document.querySelector('.nav__brand-text');
const navCloud  = document.querySelector('.nav__cloud');

if (brandText && navCloud) {
  const FADE_DISTANCE = 220;   // px of scroll over which the wordmark fades
  const GROW_TO       = 1.6;   // cloud scale at end of fade

  // Cloud-Journey-Endpunkt: Slot im Footer (data-cloud-target).
  // Wenn der Footer-Slot im Viewport näher kommt, fliegt die Cloud aus
  // dem Nav-Header in den Footer-Slot rein und schrumpft auf scale 1.
  const navCloudHost = navCloud.parentElement;          // .nav__brand-stack
  const cloudTarget  = document.querySelector('[data-cloud-target]');

  // Pixel-Position der Cloud-Mitte im Nav (nutzt --cloud-x-percent aus
  // _navigation.scss; wir nehmen 60.1% — das ist der Gap der Wortmarke).
  const NAV_CLOUD_X_PCT = 0.601;

  // Animationsfenster — getriggert über die Distanz zum Page-Ende, damit
  // die Reise ZUVERLÄSSIG bei 100 % im Footer-Slot landet, egal wie kurz
  // der Footer ist.
  //   • JOURNEY_TRIGGER_DISTANCE: px vor Page-Ende, ab wo die Reise startet
  //   • JOURNEY_END_SCALE:        Cloud-Endgröße im Footer-Slot
  const JOURNEY_TRIGGER_DISTANCE = 800;
  const JOURNEY_END_SCALE        = 1;

  const clamp01 = (n) => Math.max(0, Math.min(1, n));

  function updateLogo() {
    // ---- Phase 1: Hero-Choreografie (Wortmarke fadet, Cloud wächst) ----
    const t = clamp01(window.scrollY / FADE_DISTANCE);
    const opacity = 1 - t;
    const heroScale = 1 + (GROW_TO - 1) * t;

    brandText.style.opacity = opacity.toFixed(3);

    // ---- Phase 2: Cloud-Journey in den Footer-Slot ----
    let journeyProgress = 0;
    let dx = 0, dy = 0;

    if (cloudTarget) {
      // Progress: wie nah ist der User am Page-Ende?
      const docHeight = document.documentElement.scrollHeight;
      const vh        = window.innerHeight;
      const maxScroll = docHeight - vh;
      const fromBottom = Math.max(0, maxScroll - window.scrollY);
      journeyProgress = clamp01(1 - fromBottom / JOURNEY_TRIGGER_DISTANCE);

      // Shift: zeige permanent zur aktuellen Slot-Position im Viewport.
      // Bei progress=1 (am Page-Ende) sind cloud + slot deckungsgleich.
      const navRect    = navCloudHost.getBoundingClientRect();
      const targetRect = cloudTarget.getBoundingClientRect();

      const cloudCenterX = navRect.left + navRect.width  * NAV_CLOUD_X_PCT;
      const cloudCenterY = navRect.top  + navRect.height * 0.5;
      const slotCenterX  = targetRect.left + targetRect.width  / 2;
      const slotCenterY  = targetRect.top  + targetRect.height / 2;

      dx = slotCenterX - cloudCenterX;
      dy = slotCenterY - cloudCenterY;
    }

    // Kombinierte Skalierung: Hero-Scale (1→1.6) wird via Journey-Progress
    // wieder Richtung JOURNEY_END_SCALE zurückgeführt.
    const finalScale = heroScale - (heroScale - JOURNEY_END_SCALE) * journeyProgress;

    navCloud.style.setProperty('--cloud-scale',   finalScale.toFixed(3));
    navCloud.style.setProperty('--cloud-shift-x', `${(dx * journeyProgress).toFixed(1)}px`);
    navCloud.style.setProperty('--cloud-shift-y', `${(dy * journeyProgress).toFixed(1)}px`);
  }

  const onLogoScroll = rafThrottle(updateLogo);
  window.addEventListener('scroll', onLogoScroll, { passive: true });
  window.addEventListener('resize', onLogoScroll);
  updateLogo();
}

/* ============================================================
   1c) Mobile burger ↔ X + full-screen blue overlay menu
   ============================================================ */
const burger     = document.getElementById('navBurger');
const mobileMenu = document.getElementById('mobileMenu');

if (burger && mobileMenu) {
  const setOpen = (open) => {
    burger.classList.toggle('is-open', open);
    mobileMenu.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', String(open));
    mobileMenu.setAttribute('aria-hidden', String(!open));
    burger.setAttribute('aria-label', open ? 'Menü schließen' : 'Menü öffnen');
  };

  burger.addEventListener('click', () => {
    setOpen(!mobileMenu.classList.contains('is-open'));
  });

  // close on link tap + on Escape
  mobileMenu.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => setOpen(false));
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobileMenu.classList.contains('is-open')) setOpen(false);
  });
}

/* ============================================================
   1d) Motiv with rotate + blue-shadow drop-out
       Each `[data-motiv-shadow]` figure exposes its own per-image
       parameters via data-attributes (so every motiv can have its
       own personality without touching JS):

         data-rotate          (deg)  final rotation of the frame
         data-shadow-x        (px)   final horizontal offset of shadow
         data-shadow-y        (px)   final vertical offset of shadow
         data-shadow-rotate   (deg)  rotation of the shadow itself

       Progress is driven by the figure's position in the viewport:
       0 when its top hits the bottom of the viewport, 1 when its
       bottom leaves the top — using the centre-eased range only.
   ============================================================ */
const shadowFigures = document.querySelectorAll('[data-motiv-shadow]');

if (shadowFigures.length) {
  const ease = (t) => t * t * (3 - 2 * t); // smoothstep — same vibe as Apple

  const updateShadow = (fig) => {
    const rect = fig.getBoundingClientRect();
    const vh   = window.innerHeight;

    // Skip when fully off-screen
    if (rect.bottom < -100 || rect.top > vh + 100) return;

    // Progress: 0 when figure hasn't entered yet, 1 when it has scrolled
    // ~70% past the viewport centre.  Triggering range = 1.4 * vh,
    // so the effect plays smoothly while the user scrolls naturally.
    const triggerStart = vh;                  // top of fig touches viewport bottom
    const triggerEnd   = -rect.height * 0.4;  // mostly past centre
    const range        = triggerStart - triggerEnd;
    const raw          = (triggerStart - rect.top) / range;
    const t            = ease(Math.max(0, Math.min(1, raw)));

    const rot      = parseFloat(fig.dataset.rotate)        || 0;
    const sx       = parseFloat(fig.dataset.shadowX)       || 0;
    const sy       = parseFloat(fig.dataset.shadowY)       || 0;
    const srot     = parseFloat(fig.dataset.shadowRotate)  || 0;

    fig.style.setProperty('--motiv-rot',         (rot  * t).toFixed(2) + 'deg');
    fig.style.setProperty('--motiv-shadow-x',    (sx   * t).toFixed(2) + 'px');
    fig.style.setProperty('--motiv-shadow-y',    (sy   * t).toFixed(2) + 'px');
    fig.style.setProperty('--motiv-shadow-rot',  (srot * t).toFixed(2) + 'deg');
  };

  const onScroll = rafThrottle(() => shadowFigures.forEach(updateShadow));
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  shadowFigures.forEach(updateShadow);
}

/* ============================================================
   2) Parallax for motiv 2 — image drifts up while in viewport
   ============================================================ */
const parallaxFigure = document.querySelector('.motiv--parallax');

if (parallaxFigure) {
  const onScroll = () => {
    const rect    = parallaxFigure.getBoundingClientRect();
    const vh      = window.innerHeight;
    // only animate while the figure is reasonably close to the viewport
    if (rect.bottom < -200 || rect.top > vh + 200) return;

    // -1 .. +1 (centered = 0)
    const progress = (rect.top + rect.height / 2 - vh / 2) / vh;
    const offset   = -progress * 80; // px shift
    parallaxFigure.style.setProperty('--parallax', offset.toFixed(1) + 'px');
  };

  window.addEventListener('scroll', rafThrottle(onScroll), { passive: true });
  onScroll();
}

/* ============================================================
   3) Middle reveal for motiv 3 — backdrop rotates / scales as
      the figure enters the viewport
   ============================================================ */
const middleFigure = document.querySelector('.motiv--middle');

if (middleFigure) {
  const onScroll = () => {
    const rect = middleFigure.getBoundingClientRect();
    const vh   = window.innerHeight;
    if (rect.bottom < 0 || rect.top > vh) return;

    // 0 (just entered from below) .. 1 (perfectly centered)
    const raw      = 1 - Math.abs((rect.top + rect.height / 2 - vh / 2) / (vh / 2));
    const progress = Math.max(0, Math.min(1, raw));

    const rotation = (1 - progress) * 8;       // 8deg → 0deg
    const scale    = 0.85 + progress * 0.15;   // 0.85 → 1
    middleFigure.style.setProperty('--rot', rotation.toFixed(2) + 'deg');
    middleFigure.style.setProperty('--scl', scale.toFixed(3));
  };

  window.addEventListener('scroll', rafThrottle(onScroll), { passive: true });
  onScroll();
}

/* ============================================================
   4) Cloud trail — starts left between the images, drifts
      across to the centre as the user scrolls through the stack
   ============================================================ */
const cloud = document.getElementById('cloudTrail');
const stack = document.getElementById('motiv-stack');

if (cloud && stack) {
  const updateCloud = () => {
    const rect = stack.getBoundingClientRect();
    const vh   = window.innerHeight;

    // progress: 0 when the stack just enters the viewport, 1 when its bottom leaves the top
    const totalScrollable = rect.height + vh;
    const scrolled        = vh - rect.top;
    let progress          = scrolled / totalScrollable;
    progress              = Math.max(0, Math.min(1, progress));

    // Position relative to the stack itself
    const stackWidth = rect.width;
    const cloudW     = cloud.offsetWidth || 110;

    // X: from left edge (5%) to horizontal centre (50%)
    const startX = stackWidth * 0.05;
    const endX   = (stackWidth - cloudW) / 2;
    const x      = startX + (endX - startX) * progress;

    // Y: travel across the entire stack height
    const y      = (rect.height - 200) * progress + 80;

    // Slight rotation for character
    const rot    = -8 + progress * 16;

    cloud.style.transform = `translate3d(${x}px, ${y}px, 0) rotate(${rot}deg)`;
  };

  const onCloudScroll = rafThrottle(updateCloud);
  window.addEventListener('scroll', onCloudScroll, { passive: true });
  window.addEventListener('resize', onCloudScroll);
  updateCloud();
}

/* ============================================================
   5) Reveal-on-scroll (Apple-style)

   Zwei Layer:
     a) `.reveal` + `.is-visible`           — Section-Level Fade-in
     b) `[data-reveal]` + `.is-revealed`    — Element-Level mit
        Auto-Stagger pro Geschwister-Gruppe

   Auto-Tagging unten erspart das Verteilen von `data-reveal` im
   HTML — Kontent-Markup bleibt sauber.

   One-shot: Elemente werden nach dem ersten Triggern aus dem
   Observer entfernt, damit Hochscrollen nicht zurücksetzt.
   ============================================================ */
if ('IntersectionObserver' in window) {

  // ---- a) Section-Level ----
  const fadeTargets = document.querySelectorAll(
    '.intro, .manifest, .year, .values, .audience, .outcome, .cta-final, .motiv'
  );
  const sectionObserver = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add('is-visible');
        sectionObserver.unobserve(e.target);
      }
    }),
    { threshold: 0.12 }
  );
  fadeTargets.forEach((el) => {
    el.classList.add('reveal');
    sectionObserver.observe(el);
  });

  // ---- b) Element-Level mit Auto-Stagger ----
  // Selektoren, die granulare Reveals bekommen sollen — das HTML
  // bleibt damit clean (kein data-reveal im Markup).
  const REVEAL_SELECTORS = [
    '.card-grid > .card',         // jede Card im Card-Grid
    '.cta-final__card',            // Closing-CTA-Karten
    '.manifest__text',             // Manifest-Absätze
    '.year__block',                // Year-Textblöcke
    '.values__col',                // Values-Spalten
  ];
  REVEAL_SELECTORS.forEach((sel) => {
    document.querySelectorAll(sel).forEach((el) => {
      if (!el.hasAttribute('data-reveal')) el.setAttribute('data-reveal', '');
    });
  });

  // Bilder bekommen die scale-Variante (sanftes Reinzoomen)
  document.querySelectorAll('.motiv--shadow').forEach((el) => {
    if (!el.hasAttribute('data-reveal')) el.setAttribute('data-reveal', 'scale');
  });

  // Stagger-Delay automatisch pro Geschwister-Index berechnen
  const STAGGER_MS = 90;
  const groups = new Map();
  document.querySelectorAll('[data-reveal]').forEach((el) => {
    const parent = el.parentElement;
    if (!groups.has(parent)) groups.set(parent, []);
    groups.get(parent).push(el);
  });
  groups.forEach((siblings) => {
    siblings.forEach((el, idx) => {
      el.style.setProperty('--reveal-delay', `${(idx * STAGGER_MS) / 1000}s`);
    });
  });

  const elementObserver = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add('is-revealed');
        elementObserver.unobserve(e.target);
      }
    }),
    { threshold: 0.18, rootMargin: '0px 0px -40px 0px' }
  );
  document.querySelectorAll('[data-reveal]').forEach((el) => {
    elementObserver.observe(el);
  });
}

/* ============================================================
   Agency-Grid Filter — multi-select Toggle pro Spalte (Standort,
   Arbeitsweise, Schwerpunkte, Größe). Tiles werden ein-/ausgeblendet.
   ============================================================ */
document.querySelectorAll('[data-agency-grid]').forEach((grid) => {
  // Initial-Load: Stagger-Animation startet erst wenn das Grid in den
  // Viewport scrollt. Scroll-Listener-Fallback (IntersectionObserver
  // verhält sich in manchen Setups quirky bei sehr großen Elementen).
  let revealed = false;
  const reveal = () => {
    if (revealed) return;
    revealed = true;
    grid.classList.add('is-revealed');
    window.removeEventListener('scroll', checkInView);
  };
  const checkInView = () => {
    const r = grid.getBoundingClientRect();
    // Trigger sobald oberer Rand 80% des Viewports erreicht
    if (r.top < window.innerHeight * 0.8 && r.bottom > 0) reveal();
  };
  window.addEventListener('scroll', checkInView, { passive: true });
  // Beim Load: prüfen ob's eh schon in view ist
  checkInView();

  const filterRoot = grid.parentElement.querySelector('[data-filters]');
  if (!filterRoot) return;

  const active = {
    location: new Set(),
    arbeitsweise: new Set(),
    schwerpunkte: new Set(),
    groesse: new Set(),
  };

  const anyFilterActive = () =>
    Object.values(active).some((s) => s.size > 0);

  // Sammelt Tiles in Reihenfolge der Agentur-Pairs (img dann text).
  // Setzt explizite Grid-Positionen so dass jedes Pair in einer Reihe bleibt,
  // 2 Pairs pro Reihe (4-col grid).
  const reflowVisibleTiles = () => {
    const allTiles = Array.from(grid.querySelectorAll('.agency-grid__tile'));
    let visiblePairIdx = 0;
    // Iteriere in Pairs: img-Tile (gerade idx) + zugehöriges text-Tile (ungerade idx)
    for (let i = 0; i < allTiles.length; i += 2) {
      const imgTile = allTiles[i];
      const textTile = allTiles[i + 1];
      const imgVisible = imgTile && !imgTile.classList.contains('is-hidden');
      const textVisible = textTile && !textTile.classList.contains('is-hidden');
      if (!imgVisible && !textVisible) continue;
      // Position für dieses Pair: 2 Pairs pro Reihe → col 1+2 oder 3+4
      const pairCol = (visiblePairIdx % 2) * 2 + 1;
      const pairRow = Math.floor(visiblePairIdx / 2) + 1;
      if (imgTile) {
        imgTile.style.setProperty('--filter-col', pairCol);
        imgTile.style.setProperty('--filter-row', pairRow);
      }
      if (textTile) {
        textTile.style.setProperty('--filter-col', pairCol + 1);
        textTile.style.setProperty('--filter-row', pairRow);
      }
      visiblePairIdx++;
    }
  };

  const resetTileFlow = () => {
    grid.querySelectorAll('.agency-grid__tile').forEach((t) => {
      t.style.removeProperty('--filter-col');
      t.style.removeProperty('--filter-row');
    });
  };

  const applyFilterState = () => {
    grid.querySelectorAll('.agency-grid__tile').forEach((tile) => {
      let visible = true;
      for (const key of Object.keys(active)) {
        const wanted = active[key];
        if (wanted.size === 0) continue;
        const val = tile.dataset[key] || '';
        if (key === 'schwerpunkte') {
          const vals = val.split('|').filter(Boolean);
          if (!vals.some((v) => wanted.has(v))) { visible = false; break; }
        } else {
          if (!wanted.has(val)) { visible = false; break; }
        }
      }
      tile.classList.toggle('is-hidden', !visible);
    });
    const filtered = anyFilterActive();
    grid.classList.toggle('is-filtered', filtered);
    if (filtered) {
      reflowVisibleTiles();
    } else {
      resetTileFlow();
    }
  };

  // Filter-Update läuft instant. CSS Transitions auf opacity + transform
  // sorgen für smoothes Fading der Tiles. KEIN View-Transitions Cross-Fade,
  // weil das alte und neue Tile-Positionen kurzfristig überlappen ließ.
  const update = () => {
    applyFilterState();
  };

  filterRoot.querySelectorAll('button[data-filter]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const key = btn.dataset.filter;
      const val = btn.dataset.value;
      const set = active[key];
      if (!set) return;
      if (set.has(val)) { set.delete(val); btn.classList.remove('is-active'); }
      else              { set.add(val);    btn.classList.add('is-active'); }
      update();
    });
  });
});
