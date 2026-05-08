import '../scss/main.scss';

// Inline the brand SVG (sourced from /logo/School_of_ideas_Schriftlogo.svg)
// so its <text> nodes can pick up the document's @font-face fonts.
import brandLogoMarkup from '/logo/School_of_ideas_Schriftlogo.svg?raw';
import shootingStarMarkup from '/icons/icon-03.svg?raw';

// Strip <style>...</style> blocks from inlined SVGs — each source SVG
// carries its own `.cls-1`, `.cls-2`, … rules, and once injected they
// apply *globally* to every element on the page with the same class
// (icon-03 was painting the wordmark with a blue stroke).
const stripSvgStyle = (markup) => markup.replace(/<style[\s\S]*?<\/style>/gi, '');

document.querySelectorAll('[data-logo]').forEach(slot => {
  slot.innerHTML = stripSvgStyle(brandLogoMarkup);
});

const starSlot = document.querySelector('[data-star]');
if (starSlot) {
  starSlot.innerHTML = stripSvgStyle(shootingStarMarkup);
  // give the star a white background as in the reference
  const poly = starSlot.querySelector('polygon');
  if (poly) poly.setAttribute('fill', '#fff');
}

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
       Cloud stays put inside the logo (between "of" and "ideas").
       As the user scrolls down, the wordmark halves fade out and
       the cloud grows slightly — calmer than the horizontal travel.
   ============================================================ */
const wms      = document.querySelectorAll('.nav__wm');
const navCloud = document.querySelector('.nav__cloud');

if (wms.length && navCloud) {
  const FADE_DISTANCE = 220;   // px of scroll over which the wordmark fades
  const GROW_TO       = 1.45;  // final cloud scale at end of fade

  function updateLogo() {
    const t = Math.min(1, Math.max(0, window.scrollY / FADE_DISTANCE));
    const opacity = 1 - t;
    const scale   = 1 + (GROW_TO - 1) * t;

    wms.forEach(el => { el.style.opacity = opacity.toFixed(3); });
    navCloud.style.transform = `scale(${scale.toFixed(3)})`;
  }

  window.addEventListener('scroll', updateLogo, { passive: true });
  window.addEventListener('resize', updateLogo);
  updateLogo();
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

  window.addEventListener('scroll', onScroll, { passive: true });
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

  window.addEventListener('scroll', onScroll, { passive: true });
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

  window.addEventListener('scroll', updateCloud, { passive: true });
  window.addEventListener('resize', updateCloud);
  updateCloud();
}

/* ============================================================
   5) Soft fade-in for sections as they enter the viewport
   ============================================================ */
const fadeTargets = document.querySelectorAll('.intro, .manifest, .year, .values, .audience, .outcome, .cta-final, .motiv');

if ('IntersectionObserver' in window) {
  const io = new IntersectionObserver(
    entries => entries.forEach(e => e.isIntersecting && e.target.classList.add('is-visible')),
    { threshold: 0.12 }
  );

  fadeTargets.forEach(el => {
    el.classList.add('reveal');
    io.observe(el);
  });
}
