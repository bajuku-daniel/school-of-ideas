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
    function soi_html($text): string
    {
        if (is_object($text) && method_exists($text, 'value')) {
            $text = $text->value();
        }
        $safe = htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
        $safe = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $safe);
        $safe = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $safe);
        return nl2br($safe);
    }
}

if (!function_exists('soi_paragraphs')) {
    function soi_paragraphs($text, string $leadClass = ''): string
    {
        if (is_object($text) && method_exists($text, 'value')) {
            $text = $text->value();
        }
        $parts = preg_split('/\R{2,}/', trim((string)$text)) ?: [];
        $html = '';
        foreach ($parts as $index => $part) {
            if (trim($part) === '') continue;
            $class = $index === 0 && $leadClass !== '' ? ' class="' . $leadClass . '"' : '';
            $html .= '<p' . $class . '>' . soi_html(trim($part)) . '</p>';
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
     */
    function soi_icon_text(string $text, array $localPool = [], string $extraClass = 'manifest__icon'): string
    {
        $escaped = soi_html($text);
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
