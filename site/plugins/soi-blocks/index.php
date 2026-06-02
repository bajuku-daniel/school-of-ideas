<?php

/**
 * School of Ideas — geteilte Helpers für Templates + Block-Snippets.
 *
 * Diese Helper waren früher inline in home.php definiert. Sie sind jetzt
 * zentral, damit Block-Snippets in /snippets/blocks/ sie ebenfalls nutzen.
 */

if (!function_exists('soi_text')) {
    function soi_text($source, string $field, string $fallback = ''): string
    {
        if (!is_object($source)) return $fallback;
        try {
            $value = $source->{$field}();
            if (is_object($value) && $value->isNotEmpty()) {
                return $value->value();
            }
        } catch (\Throwable $e) {}
        return $fallback;
    }
}

if (!function_exists('soi_html')) {
    /**
     * Markup im Editor:
     *   **text**  → <strong> (bold)
     *   *text*    → <em>     (kursiv)
     *   --text--  → <span class="accent">  (Marken-Blau-Akzent)
     */
    function soi_html($text): string
    {
        if (is_object($text) && method_exists($text, 'value')) {
            $text = $text->value();
        }
        $safe = htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
        // Bold zuerst (** sind länger, vermeiden Konflikte mit *)
        $safe = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $safe);
        // Italic (kein doppeltes Stern)
        $safe = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $safe);
        // Inline-Accent: --text-- → blauer Span. Funktioniert auch mitten im
        // Satz, z. B. "Wir lieben Fragen, --wir lieben die bessere Idee.--"
        // Negative-Lookahead erlaubt einzelne Bindestriche im Inhalt
        // (z. B. "Kreativ-Agentur"), aber kein zweites "--" innendrin.
        $safe = preg_replace('/--((?:(?!--).)+?)--/s', '<span class="accent">$1</span>', $safe);
        return nl2br($safe);
    }
}

if (!function_exists('soi_paragraphs')) {
    /**
     * Rendert einen Fließtext zu <p>-Absätzen. Markdown-Inline-Format (**bold**,
     * *italic*) bleibt erhalten.
     *
     * **Steuerung der Schriftgröße pro Absatz:**
     * Ein Absatz, der KOMPLETT von `**...**` umschlossen ist, wird mit der
     * Klasse $leadClass (z.B. 'text-medium') gerendert — anstatt mit
     * <strong> versehen. So kann der Designer pro Absatz entscheiden, ob er
     * groß/bold gerendert werden soll.
     *
     * Beispiel:
     *   Text: "**Erster Absatz fett**\n\nNormaler Absatz mit **inline-bold**."
     *   →    <p class="text-medium">Erster Absatz fett</p>
     *        <p>Normaler Absatz mit <strong>inline-bold</strong>.</p>
     *
     * Vorher gab's einen "erster-Absatz-immer-medium" Auto-Modus. Der ist raus,
     * weil der Designer das jetzt explizit steuern können soll.
     */
    function soi_paragraphs($text, string $leadClass = 'text-medium'): string
    {
        if (is_object($text) && method_exists($text, 'value')) {
            $text = $text->value();
        }
        $parts = preg_split('/\R{2,}/', trim((string)$text)) ?: [];
        $html = '';
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;

            // Ganzer Absatz in **...** → als CSS-Klasse rendern (kein <strong>).
            // Class bringt Medium-Weight (500) für den ruhigen Lead-Look,
            // kein zusätzliches Bold (700). Inline-bold in normalen Absätzen
            // (`Text mit **fett** drin`) bleibt davon unberührt — das macht
            // soi_html weiterhin via <strong>.
            // Negative-Lookahead `(?!\*\*)` verhindert, dass z.B.
            // "**A** und **B**" matched.
            if ($leadClass !== '' && preg_match('/^\*\*((?:(?!\*\*).)+)\*\*$/s', $part, $m)) {
                $inner = trim($m[1]);
                $html .= '<p class="' . $leadClass . '">' . soi_html($inner) . '</p>';
            } else {
                $html .= '<p>' . soi_html($part) . '</p>';
            }
        }
        return $html;
    }
}

if (!function_exists('soi_file_url')) {
    /**
     * Holt URL aus einem files-Feld (1 File). Optional mit Cache-Buster.
     * Kirby Field-Methoden (toFiles, toFile, isNotEmpty) sind __call-magisch,
     * deshalb hier KEIN method_exists check.
     */
    function soi_file_url($field, bool $bust = false): ?string
    {
        if (!is_object($field)) return null;

        // toFiles() für files-Felder mit multiple/1 max
        try {
            $file = $field->toFiles()->first();
        } catch (\Throwable $e) {
            $file = null;
        }

        // toFile() für single-File-Felder mit UUID-String
        if (!$file) {
            try {
                $file = $field->toFile();
            } catch (\Throwable $e) {
                $file = null;
            }
        }

        if ($file) {
            return $file->url() . ($bust ? '?v=' . $file->modified() : '');
        }

        // Fallback: roher String → url()
        try {
            if ($field->isNotEmpty()) {
                $raw = $field->value();
                if (is_string($raw) && $raw !== '') {
                    return url(ltrim($raw, '/'));
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }
}

if (!function_exists('soi_image_focus_style')) {
    /**
     * Liest den Focal Point aus einem Kirby-Bild und gibt einen
     * `object-position: X% Y%` String zurück. Wirkt zusammen mit
     * `object-fit: cover` damit der Designer-gewählte Bildmittelpunkt
     * sichtbar bleibt, statt dass das Bild zufällig zentriert beschnitten wird.
     *
     * Gibt leeren String zurück wenn:
     *   - kein Bild im Feld
     *   - kein Focus gesetzt (Kirby-Default = 50% 50% greift dann eh)
     */
    function soi_image_focus_style($fieldOrFile): string
    {
        if (!is_object($fieldOrFile)) return '';
        try {
            // Direkt ein File-Object (z.B. in Loops mit $img->url())
            if ($fieldOrFile instanceof \Kirby\Cms\File) {
                $file = $fieldOrFile;
            } else {
                $file = $fieldOrFile->toFiles()->first();
                if (!$file) {
                    $file = $fieldOrFile->toFile();
                }
            }
            if (!$file) return '';

            $focus = $file->focus();

            // Werte als Prozent (0..100) extrahieren — egal welches Speicher-Format.
            $px = null; $py = null;

            // 1) FocusValue-Object mit ->x()/->y() (Kirby v4 hat 0..1, v5 evtl. anders)
            if (is_object($focus) && method_exists($focus, 'x') && method_exists($focus, 'y')) {
                $x = (float)$focus->x();
                $y = (float)$focus->y();
                // Wenn beide ≤ 1 → 0..1 Range, sonst schon 0..100
                $px = ($x <= 1.0 && $y <= 1.0) ? $x * 100 : $x;
                $py = ($x <= 1.0 && $y <= 1.0) ? $y * 100 : $y;
            }
            // 2) Field mit raw value
            elseif (is_object($focus) && method_exists($focus, 'value')) {
                $raw = trim((string)$focus->value());
                if ($raw !== '') {
                    // Kirby v5: "66.0% 33.6%" (Prozent, Space-getrennt)
                    // Kirby v4 / Plugins: "0.66, 0.336" (Decimal, Komma)
                    // Beides parsen: alle nicht-numerischen Zeichen (außer . und -)
                    // raus, dann splitten
                    if (preg_match_all('/-?\d+(?:\.\d+)?/', $raw, $m) && count($m[0]) === 2) {
                        $x = (float)$m[0][0];
                        $y = (float)$m[0][1];
                        // Prozent-Suffix vorhanden? → schon 0..100
                        $isPercent = strpos($raw, '%') !== false;
                        $px = ($isPercent || $x > 1.0 || $y > 1.0) ? $x : $x * 100;
                        $py = ($isPercent || $x > 1.0 || $y > 1.0) ? $y : $y * 100;
                    }
                }
            }

            if ($px === null || $py === null) return '';
            // Default 50/50 → CSS-Default greift, kein Override nötig
            if (abs($px - 50) < 0.5 && abs($py - 50) < 0.5) return '';
            return 'object-position:' . round($px, 1) . '% ' . round($py, 1) . '%';
        } catch (\Throwable $e) {}
        return '';
    }
}

if (!function_exists('soi_image_focus_attr')) {
    /**
     * Convenience: rendert direkt ein style="…" Attribut (oder leeren String).
     * Nutzung im Snippet:
     *   <img class="motiv__img" src="…"<?= soi_image_focus_attr($field) ?> alt="">
     */
    function soi_image_focus_attr($field): string
    {
        $style = soi_image_focus_style($field);
        return $style === '' ? '' : ' style="' . esc($style, 'attr') . '"';
    }
}

if (!function_exists('soi_inline_icon')) {
    /**
     * Inline-Icon im Text via {icon:name} Token.
     * Sucht zuerst im lokalen Pool des Blocks (z.B. highlight-text),
     * dann globale iconLibrary, dann Datei-Fallback aus /icons/.
     */
    function soi_inline_icon(string $name, array $localPool = [], string $extraClass = ''): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
        if ($name === '') return '';
        $class = 'site-icon' . ($extraClass !== '' ? ' ' . $extraClass : '');

        // 1) Lokaler Block-Pool
        if (isset($localPool[$name])) {
            return '<span class="' . $class . '" aria-hidden="true"><img class="site-icon__img" src="' . esc($localPool[$name]) . '" alt=""></span>';
        }

        // 2) Globale Library (alt-System, Übergang)
        $global = $GLOBALS['soiIconLibrary'] ?? [];
        if (isset($global[$name])) {
            return '<span class="' . $class . '" aria-hidden="true"><img class="site-icon__img" src="' . esc($global[$name]) . '" alt=""></span>';
        }

        // 3) SVG-Fallback
        return '<span class="' . $class . '" aria-hidden="true"><img class="site-icon__svg" src="' . url('icons/' . $name . '.svg') . '" alt=""></span>';
    }
}

if (!function_exists('soi_icon_text')) {
    /**
     * Ersetzt {icon:name} im Text durch Inline-Icons.
     * $localPool ist [name => url].
     *
     * Zusätzlich: leading-Whitespace vor jedem Icon wird zu NBSP. So bleibt
     * das Icon immer am vorigen Wort kleben und macht keinen Orphan-Umbruch.
     */
    function soi_icon_text(string $text, array $localPool = [], string $extraClass = 'manifest__icon'): string
    {
        $escaped = soi_html($text);
        // NBSP statt Space vor Icon-Token → bleibt am vorigen Wort
        $escaped = preg_replace('/[ \t]+(?=\{icon:)/u', '&nbsp;', $escaped);
        return preg_replace_callback('/\{icon:([a-zA-Z0-9_-]+)\}/', function ($m) use ($localPool, $extraClass) {
            return soi_inline_icon($m[1], $localPool, $extraClass);
        }, $escaped);
    }
}

if (!function_exists('soi_block_icon_pool')) {
    /**
     * Lokaler Icon-Pool eines Blocks. Erwartet ein structure-Feld "iconPool"
     * mit Items {key, file (files:1)}. Gibt [key => url] zurück.
     */
    function soi_block_icon_pool($block): array
    {
        if (!is_object($block)) return [];
        $pool = [];
        try {
            foreach ($block->iconPool()->toStructure() as $item) {
                $key = trim((string)$item->key());
                if ($key === '') continue;
                $file = $item->file()->toFiles()->first();
                if ($file) {
                    $pool[$key] = $file->url();
                }
            }
        } catch (\Throwable $e) {}
        return $pool;
    }
}

if (!function_exists('soi_section_attrs')) {
    /**
     * Baut die <section>-Attribute (Klassen + style) für einen Block.
     *
     * Liest die geteilten Wrapper-Felder:
     *   - sectionTheme (light/mint/blue/none)
     *   - anchorId
     *   - spacingTop/Bottom × Desktop/Tablet/Mobile (px)
     *
     * Plus extra block-spezifische CSS-Variablen via $extraVars
     *   ['--my-var-desktop' => 120, ...]
     */
    function soi_section_attrs($block, string $baseClass, array $extraVars = []): string
    {
        $classes = [$baseClass];
        $theme = (string)$block->sectionTheme();
        if ($theme !== '' && $theme !== 'none') {
            $classes[] = $baseClass . '--theme-' . $theme;
            $classes[] = 'section--' . $theme;
        }

        $styles = [];
        $spacingMap = [
            'spacingTopDesktop'    => '--section-top-desktop',
            'spacingTopTablet'     => '--section-top-tablet',
            'spacingTopMobile'     => '--section-top-mobile',
            'spacingBottomDesktop' => '--section-bottom-desktop',
            'spacingBottomTablet'  => '--section-bottom-tablet',
            'spacingBottomMobile'  => '--section-bottom-mobile',
            'subGapDesktop'        => '--section-sub-gap-desktop',
            'subGapTablet'         => '--section-sub-gap-tablet',
            'subGapMobile'         => '--section-sub-gap-mobile',
            'bodyGapDesktop'       => '--section-body-gap-desktop',
            'bodyGapTablet'        => '--section-body-gap-tablet',
            'bodyGapMobile'        => '--section-body-gap-mobile',
            // Backwards-Compat: alte headGap-Felder fallen auf body-gap (war
            // semantisch dasselbe) — falls jemand schon Werte eingetragen hat.
            'headGapDesktop'       => '--section-body-gap-desktop',
            'headGapTablet'        => '--section-body-gap-tablet',
            'headGapMobile'        => '--section-body-gap-mobile',
        ];
        foreach ($spacingMap as $field => $var) {
            try {
                $val = $block->{$field}();
                if ($val->isNotEmpty()) {
                    $styles[] = $var . ':' . (int)$val->value() . 'px';
                }
            } catch (\Throwable $e) {}
        }

        foreach ($extraVars as $var => $value) {
            if ($value === null || $value === '') continue;
            $styles[] = $var . ':' . $value;
        }

        $attrs = ' class="' . esc(implode(' ', $classes), 'attr') . '"';
        if (!empty($styles)) {
            $attrs .= ' style="' . esc(implode(';', $styles), 'attr') . '"';
        }
        $anchor = (string)$block->anchorId();
        if ($anchor !== '') {
            $attrs .= ' id="' . esc($anchor, 'attr') . '"';
        }
        return $attrs;
    }
}

if (!function_exists('soi_section_icon_url')) {
    /**
     * Resolvet die Icon-URL für einen Breakpoint.
     * Reihenfolge:
     *   1. Eigener Upload pro BP (iconMobile/iconTablet/iconDesktop)
     *   2. iconKey + /icons/png/<bp>/<key>.png (Bibliothek)
     */
    function soi_section_icon_url($block, string $bp, ?string $iconKey = null): ?string
    {
        $field = 'icon' . ucfirst($bp);
        try {
            $file = $block->{$field}()->toFiles()->first();
            if ($file) return $file->url();
        } catch (\Throwable $e) {}

        if ($iconKey !== null && $iconKey !== '') {
            // Aus Filesystem-Library prüfen, ob's existiert
            $kirby = kirby();
            $relPath = 'icons/png/' . $bp . '/' . $iconKey . '.png';
            $absPath = $kirby->root('index') . '/' . $relPath;
            if (is_file($absPath)) {
                return url($relPath);
            }
        }
        return null;
    }
}

if (!function_exists('soi_library_icon_url')) {
    /**
     * URL zu einem Library-Icon für einen Breakpoint (kein Upload-Fallback).
     * Liefert null wenn die PNG-Datei nicht existiert.
     */
    function soi_library_icon_url(string $key, string $bp): ?string
    {
        $key = trim($key);
        if ($key === '') return null;
        if (!in_array($bp, ['mobile', 'tablet', 'desktop'], true)) return null;

        $relPath = 'icons/png/' . $bp . '/' . $key . '.png';
        $absPath = kirby()->root('index') . '/' . $relPath;
        if (is_file($absPath)) {
            return url($relPath);
        }
        return null;
    }
}

if (!function_exists('soi_library_picture')) {
    /**
     * Rendert ein <picture>-Element für ein Library-Icon mit allen 3
     * Strichstärken (Mobile/Tablet/Desktop). Browser pickt automatisch
     * die passende per media-query.
     *
     * Breakpoints korrespondieren mit SCSS:
     *   - mobile:  bis 768px
     *   - tablet:  769-1199px
     *   - desktop: ab 1200px
     *
     * Wenn $uploadUrl gesetzt ist, hat das Vorrang vor der Library
     * (eigener Upload überschreibt Bibliothek-Auswahl).
     */
    function soi_library_picture(
        ?string $key,
        ?string $uploadUrl = null,
        string $class = '',
        string $alt = ''
    ): string {
        // Upload-Override: nur ein <img>, kein <picture> nötig
        if ($uploadUrl !== null && $uploadUrl !== '') {
            $attrs = '';
            if ($class !== '') $attrs .= ' class="' . esc($class, 'attr') . '"';
            return '<img src="' . esc($uploadUrl, 'attr') . '" alt="' . esc($alt, 'attr') . '"' . $attrs . '>';
        }

        $key = $key ? trim($key) : '';
        if ($key === '') return '';

        $mobile  = soi_library_icon_url($key, 'mobile');
        $tablet  = soi_library_icon_url($key, 'tablet');
        $desktop = soi_library_icon_url($key, 'desktop');

        // Fallback: wenn ein BP fehlt, den nächst-besseren nehmen
        $fallback = $desktop ?? $tablet ?? $mobile;
        if ($fallback === null) return '';

        $mobile  = $mobile  ?? $fallback;
        $tablet  = $tablet  ?? $fallback;
        $desktop = $desktop ?? $fallback;

        $cls = $class !== '' ? ' class="' . esc($class, 'attr') . '"' : '';
        $altAttr = ' alt="' . esc($alt, 'attr') . '"';

        return '<picture>'
            . '<source media="(min-width: 1200px)" srcset="' . esc($desktop, 'attr') . '">'
            . '<source media="(min-width: 769px)" srcset="' . esc($tablet, 'attr') . '">'
            . '<img src="' . esc($mobile, 'attr') . '"' . $altAttr . $cls . '>'
            . '</picture>';
    }
}

if (!function_exists('soi_section_icon')) {
    /**
     * Rendert das Section-Icon (Library oder eigener Upload, pro BP).
     * Plus CSS-Vars für Position, Größe, Rotation, Wobble-Animation.
     */
    function soi_section_icon($block): string
    {
        if (!is_object($block)) return '';

        $iconKey = '';
        try { $iconKey = trim((string)$block->iconKey()); } catch (\Throwable $e) {}

        $urls = [
            'mobile'  => soi_section_icon_url($block, 'mobile',  $iconKey),
            'tablet'  => soi_section_icon_url($block, 'tablet',  $iconKey),
            'desktop' => soi_section_icon_url($block, 'desktop', $iconKey),
        ];

        // Falls gar nichts da ist → nichts rendern
        if (!$urls['desktop'] && !$urls['tablet'] && !$urls['mobile']) {
            return '';
        }

        // Fallback-Kaskade
        if (!$urls['tablet'])  $urls['tablet']  = $urls['desktop'] ?? $urls['mobile'];
        if (!$urls['mobile'])  $urls['mobile']  = $urls['tablet']  ?? $urls['desktop'];
        if (!$urls['desktop']) $urls['desktop'] = $urls['tablet']  ?? $urls['mobile'];

        // Per-BP Position/Größe/Rotation als CSS-Vars
        $styles = [];
        $bps = ['mobile', 'tablet', 'desktop'];
        $props = [
            'X'      => 'x',
            'Y'      => 'y',
            'Size'   => 'size',
            'Rotate' => 'rotate',
        ];
        foreach ($bps as $bp) {
            foreach ($props as $suffix => $cssName) {
                $field = 'icon' . ucfirst($bp) . $suffix;
                try {
                    $val = $block->{$field}();
                    if ($val->isNotEmpty()) {
                        $unit = $cssName === 'rotate' ? 'deg' : 'px';
                        $styles[] = "--section-icon-{$cssName}-{$bp}:" . (int)$val->value() . $unit;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Animation-Konfiguration
        $motion   = 'wobble';
        $duration = '6.5s';
        $delay    = '0s';
        try {
            $m = (string)$block->iconMotion();
            if ($m !== '') $motion = $m;
            $d = $block->iconMotionDuration();
            if ($d->isNotEmpty()) $duration = ((float)$d->value()) . 's';
            $dl = $block->iconMotionDelay();
            if ($dl->isNotEmpty()) $delay = ((float)$dl->value()) . 's';
        } catch (\Throwable $e) {}

        $styles[] = '--icon-motion-name:' . ($motion === 'none' ? 'none' : ($motion === 'glide' ? 'floatGlide' : 'floatWobble'));
        $styles[] = '--icon-motion-duration:' . $duration;
        $styles[] = '--icon-motion-delay:' . $delay;

        $html = '<span class="section-icon" data-motion="' . esc($motion, 'attr') . '" aria-hidden="true"';
        if (!empty($styles)) {
            $html .= ' style="' . esc(implode(';', $styles), 'attr') . '"';
        }
        $html .= '>';
        foreach ($bps as $bp) {
            if ($urls[$bp]) {
                $html .= '<img class="section-icon__img section-icon__img--' . $bp . '" src="' . esc($urls[$bp]) . '" alt="" loading="lazy">';
            }
        }
        $html .= '</span>';
        return $html;
    }
}

if (!function_exists('soi_section_icon_v2')) {
    /**
     * v2 Section-Icon Renderer — Anker-basiert.
     *
     * Liest neue Felder:
     *   iconKey, iconRef (section|image),
     *   iconAnchorY (top|middle|bottom), iconAnchorX (left|center|right),
     *   iconOffsetX, iconOffsetY, iconRotate,
     *   iconMobileSize/iconTabletSize/iconDesktopSize,
     *   iconMobile/iconTablet/iconDesktop (Datei-Overrides),
     *   iconMotion/iconMotionDuration/iconMotionDelay.
     *
     * Output HTML:
     *   <span class="section-icon section-icon--v2"
     *         data-anchor-y="top" data-anchor-x="right" data-ref="section"
     *         style="--icon-offset-x:10px;--icon-offset-y:-20px;
     *                --icon-rotate:0deg;
     *                --icon-size-mobile:60px; --icon-size-tablet:90px;
     *                --icon-size-desktop:120px;
     *                --icon-motion-name:floatWobble;...">
     *     <img class="section-icon__img section-icon__img--mobile" ...>
     *     <img class="section-icon__img section-icon__img--tablet" ...>
     *     <img class="section-icon__img section-icon__img--desktop" ...>
     *   </span>
     *
     * Bezug = "image": Snippet sollte das Icon-HTML innerhalb der figure
     * rendern (das figure ist position:relative). Bezug = "section": Icon
     * direkt im section. Diese Helper-Funktion gibt nur das Markup zurück —
     * wo es im Snippet platziert wird, entscheidet das Snippet.
     */
    function soi_section_icon_v2($block): string
    {
        if (!is_object($block)) return '';

        $iconKey = '';
        try { $iconKey = trim((string)$block->iconKey()); } catch (\Throwable $e) {}

        $urls = [
            'mobile'  => soi_section_icon_url($block, 'mobile',  $iconKey),
            'tablet'  => soi_section_icon_url($block, 'tablet',  $iconKey),
            'desktop' => soi_section_icon_url($block, 'desktop', $iconKey),
        ];

        if (!$urls['desktop'] && !$urls['tablet'] && !$urls['mobile']) {
            return '';
        }

        // Fallback-Kaskade
        if (!$urls['tablet'])  $urls['tablet']  = $urls['desktop'] ?? $urls['mobile'];
        if (!$urls['mobile'])  $urls['mobile']  = $urls['tablet']  ?? $urls['desktop'];
        if (!$urls['desktop']) $urls['desktop'] = $urls['tablet']  ?? $urls['mobile'];

        // Anker (Defaults: oben-rechts)
        $anchorY = 'top';
        $anchorX = 'right';
        $ref     = 'section';
        try {
            $ay = (string)$block->iconAnchorY();
            if (in_array($ay, ['top', 'middle', 'bottom'], true)) $anchorY = $ay;
            $ax = (string)$block->iconAnchorX();
            if (in_array($ax, ['left', 'center', 'right'], true)) $anchorX = $ax;
            $r  = (string)$block->iconRef();
            if (in_array($r,  ['section', 'image'], true))         $ref     = $r;
        } catch (\Throwable $e) {}

        // CSS-Vars
        $styles = [];

        // Offsets + Rotation (jeweils mit 0 als Default)
        $reads = [
            'iconOffsetX'      => '--icon-offset-x',
            'iconOffsetY'      => '--icon-offset-y',
            'iconRotate'       => '--icon-rotate',
        ];
        foreach ($reads as $field => $cssVar) {
            try {
                $v = $block->{$field}();
                if ($v->isNotEmpty()) {
                    $unit = ($cssVar === '--icon-rotate') ? 'deg' : 'px';
                    $styles[] = $cssVar . ':' . (int)$v->value() . $unit;
                }
            } catch (\Throwable $e) {}
        }

        // Größe pro BP
        foreach (['mobile', 'tablet', 'desktop'] as $bp) {
            $field = 'icon' . ucfirst($bp) . 'Size';
            try {
                $v = $block->{$field}();
                if ($v->isNotEmpty()) {
                    $styles[] = '--icon-size-' . $bp . ':' . (int)$v->value() . 'px';
                }
            } catch (\Throwable $e) {}
        }

        // Animation
        $motion   = 'wobble';
        $duration = '6.5s';
        $delay    = '0s';
        try {
            $m = (string)$block->iconMotion();
            if ($m !== '') $motion = $m;
            $d = $block->iconMotionDuration();
            if ($d->isNotEmpty()) $duration = ((float)$d->value()) . 's';
            $dl = $block->iconMotionDelay();
            if ($dl->isNotEmpty()) $delay = ((float)$dl->value()) . 's';
        } catch (\Throwable $e) {}

        $styles[] = '--icon-motion-name:' . ($motion === 'none' ? 'none' : ($motion === 'glide' ? 'floatGlide' : 'floatWobble'));
        $styles[] = '--icon-motion-duration:' . $duration;
        $styles[] = '--icon-motion-delay:' . $delay;

        $html  = '<span class="section-icon section-icon--v2"';
        $html .= ' data-anchor-y="' . esc($anchorY, 'attr') . '"';
        $html .= ' data-anchor-x="' . esc($anchorX, 'attr') . '"';
        $html .= ' data-ref="' . esc($ref, 'attr') . '"';
        $html .= ' data-motion="' . esc($motion, 'attr') . '"';
        $html .= ' aria-hidden="true"';
        if (!empty($styles)) {
            $html .= ' style="' . esc(implode(';', $styles), 'attr') . '"';
        }
        $html .= '>';
        foreach (['mobile', 'tablet', 'desktop'] as $bp) {
            if ($urls[$bp]) {
                $html .= '<img class="section-icon__img section-icon__img--' . $bp . '" src="' . esc($urls[$bp]) . '" alt="" loading="lazy">';
            }
        }
        $html .= '</span>';
        return $html;
    }
}

if (!function_exists('soi_section_icon_ref')) {
    /**
     * Helper für Snippets: wo soll das Icon im DOM gerendert werden?
     * Returns 'image' oder 'section'. Snippet kann damit entscheiden ob
     * das Icon in figure (Bild) oder direkt in section gehört.
     */
    function soi_section_icon_ref($block): string
    {
        try {
            $r = (string)$block->iconRef();
            if (in_array($r, ['section', 'image'], true)) return $r;
        } catch (\Throwable $e) {}
        return 'section';
    }
}

if (!function_exists('soi_section_icon_at')) {
    /**
     * Convenience-Helper für Snippets: rendert das v2-Icon nur dann,
     * wenn der User diese Position (image/section) gewählt hat.
     *
     * Usage in Snippet:
     *   <figure>
     *     ...
     *     <?= soi_section_icon_at($block, 'image') ?>
     *   </figure>
     *   ...
     *   <?= soi_section_icon_at($block, 'section') ?>
     *
     * Pro Block beide Aufrufe rein — der Helper entscheidet wo es erscheint.
     * Wenn der Block kein Bild hat: einfach nur den 'section'-Aufruf nutzen.
     */
    function soi_section_icon_at($block, string $location): string
    {
        if (!in_array($location, ['section', 'image'], true)) return '';
        if (soi_section_icon_ref($block) !== $location) return '';
        return soi_section_icon_v2($block);
    }
}

if (!function_exists('soi_image_shadow_style')) {
    /**
     * CSS-Vars für den blauen Bild-Shadow (Per-BP X/Y/Rotate-Offset).
     */
    function soi_image_shadow_style($block): string
    {
        if (!is_object($block)) return '';
        $styles = [];
        $bps = ['mobile', 'tablet', 'desktop'];
        $props = [
            'shadowX'      => 'shadow-x',
            'shadowY'      => 'shadow-y',
            'shadowRotate' => 'shadow-rot',
            'motivRotate'  => 'motiv-rot',
        ];
        foreach ($bps as $bp) {
            foreach ($props as $field => $cssName) {
                $fieldName = $field . ucfirst($bp);
                try {
                    $val = $block->{$fieldName}();
                    if ($val->isNotEmpty()) {
                        $unit = str_contains($cssName, 'rot') ? 'deg' : 'px';
                        $styles[] = "--motiv-{$cssName}-{$bp}:" . (int)$val->value() . $unit;
                    }
                } catch (\Throwable $e) {}
            }
        }
        return implode(';', $styles);
    }
}

if (!function_exists('soi_image_shadow_data_attrs')) {
    /**
     * Renderbares Attribut-Set für die JS-Scroll-Animation (motiv-shadow.js).
     * Liest die DESKTOP-Werte (die JS animiert nur einen Satz, kein BP-Switching).
     * Output z. B.: data-motiv-shadow data-rotate="20" data-shadow-x="56" ...
     */
    function soi_image_shadow_data_attrs($block): string
    {
        if (!is_object($block)) return ' data-motiv-shadow';
        $map = [
            'motivRotateDesktop'  => 'data-rotate',
            'shadowXDesktop'      => 'data-shadow-x',
            'shadowYDesktop'      => 'data-shadow-y',
            'shadowRotateDesktop' => 'data-shadow-rotate',
        ];
        $out = ' data-motiv-shadow';
        foreach ($map as $field => $attr) {
            try {
                $val = $block->{$field}();
                if ($val->isNotEmpty()) {
                    $out .= ' ' . $attr . '="' . esc((string)(int)$val->value(), 'attr') . '"';
                }
            } catch (\Throwable $e) {}
        }
        return $out;
    }
}
