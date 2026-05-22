<?php
/**
 * Patch-Script: Nachträgliches Prefill der Section-Icons + Shadow-Werte
 * für die migrierten Home-Blocks.
 *
 * - Setzt iconKey + Default-Position aus den alten decorativeIcons-Slots
 * - Setzt shadow*Desktop Werte aus den alten data-attributes im Template
 * - Aktiviert wobble-Animation als Default
 *
 * Idempotent: wird nur gesetzt, wenn das Feld leer ist (überschreibt keine
 * manuellen Panel-Edits).
 *
 * Ausführung:
 *   ddev exec php /var/www/html/scripts/patch-home-prefills.php
 */

if (php_sapi_name() !== 'cli') {
    echo "Bitte nur per CLI ausführen.\n";
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';
$root = dirname(__DIR__);

$kirby = new Kirby([
    'roots' => [
        'index'   => $root . '/public',
        'content' => $root . '/content',
        'kirby'   => $root . '/kirby',
        'site'    => $root . '/site',
    ],
]);

$kirby->impersonate('kirby');
$page = $kirby->page('home');
if (!$page) { echo "Home nicht gefunden.\n"; exit(1); }

// Bestehende Builder-Blocks lesen
$raw = $page->builder()->value();
$blocks = is_array($raw) ? $raw : json_decode((string)$raw, true);
if (!is_array($blocks) || empty($blocks)) {
    echo "Kein Builder-Inhalt gefunden. Erst migrate-home-to-blocks.php laufen lassen.\n";
    exit(1);
}

// Per-Block-Type Prefill-Defaults.
// iconKey = dominantes Icon aus den alten Float-Icons der Original-Sektion.
// position: Werte aus _content.scss übersetzt (desktop). Mobile/Tablet bekommt
// proportional kleinere Werte; falls leer → CSS-Default.
// shadow: aus den alten data-attributes im hardcoded Template (vor Refactor).
$presetByType = [
    // .intro hatte Lightning (links) + Airplane (rechts). Wir nehmen Airplane
    // als primäres section-icon weil's prominenter platziert war.
    'split-intro' => [
        'iconKey'              => 'airplane',
        'iconDesktopX'         => 30,
        'iconDesktopY'         => 720,    // unten-rechts der rechten Spalte
        'iconDesktopSize'      => 210,
        'iconDesktopRotate'    => 0,
        'iconTabletX'          => 40,
        'iconTabletY'          => 540,
        'iconTabletSize'       => 0,      // war auf tablet ausgeblendet
        'iconMobileSize'       => 0,      // war mobil ausgeblendet
        'iconMotion'           => 'glide',
        'iconMotionDuration'   => 8.6,
        'iconMotionDelay'      => -3.4,
        // Shadow (aus altem Template: data-rotate="-15" x=0 y=0 srot=15)
        'motivRotateDesktop'   => -15,
        'shadowXDesktop'       => 0,
        'shadowYDesktop'       => 0,
        'shadowRotateDesktop'  => 15,
    ],
    // .expect hatte airplane + shooting-star + bottle. shooting-star als Hero.
    'editorial-split-intro' => [ /* setzt je nach layout in der Loop unten */ ],
    // .audience hatte 4 Float-Icons (shooting/comet/airplane/spark)
    'editorial-bullet-list' => [
        'iconKey'             => 'shooting-star',
        'iconDesktopSize'     => 120,
        'iconDesktopX'        => 80,
        'iconDesktopY'        => 80,
        'iconMotion'          => 'wobble',
        'iconMotionDuration'  => 6.5,
    ],
    // .cta-final hatte document + shooting-star + bottle + glasses
    'conversion-section' => [
        'iconKey'             => 'glasses-bold',
        'iconDesktopSize'     => 140,
        'iconDesktopX'        => 80,
        'iconDesktopY'        => 200,
        'iconMotion'          => 'wobble',
        // Shadow: data-rotate="-5" x="-60" y=50 srot=6
        'motivRotateDesktop'  => -5,
        'shadowXDesktop'      => -60,
        'shadowYDesktop'      => 50,
        'shadowRotateDesktop' => 6,
    ],
];

// editorial-split-intro Layouts separat behandeln (.expect/.year/.values)
$editorialByLayout = [
    '1col' => [  // .expect
        'iconKey'             => 'shooting-star',
        'iconDesktopSize'     => 110,
        'iconDesktopX'        => 60,
        'iconDesktopY'        => 100,
        'iconMotion'          => 'wobble',
        // Shadow: data-rotate=20 x=56 y=48 srot=-9
        'motivRotateDesktop'  => 20,
        'shadowXDesktop'      => 56,
        'shadowYDesktop'      => 48,
        'shadowRotateDesktop' => -9,
    ],
    '2col' => [  // .year
        'iconKey'             => 'sparkles',
        'iconDesktopSize'     => 120,
        'iconDesktopX'        => 80,
        'iconDesktopY'        => 100,
        'iconMotion'          => 'wobble',
        // Shadow: data-rotate=-5 x=-50 y=40 srot=7
        'motivRotateDesktop'  => -5,
        'shadowXDesktop'      => -50,
        'shadowYDesktop'      => 40,
        'shadowRotateDesktop' => 7,
    ],
    '3col' => [  // .values
        'iconKey'             => 'spark',
        'iconDesktopSize'     => 100,
        'iconDesktopX'        => 60,
        'iconDesktopY'        => 60,
        'iconMotion'          => 'wobble',
    ],
];

$touched = 0;
$skippedTouches = 0;

foreach ($blocks as &$block) {
    $type = $block['type'] ?? '';
    $content = $block['content'] ?? [];

    $presets = [];
    if ($type === 'editorial-split-intro') {
        $layout = $content['layout'] ?? '1col';
        $presets = $editorialByLayout[$layout] ?? [];
    } else {
        $presets = $presetByType[$type] ?? [];
    }

    foreach ($presets as $key => $value) {
        // Kirby normalisiert keys zu lowercase im content-array
        $lcKey = strtolower($key);
        $existing = $content[$lcKey] ?? $content[$key] ?? '';
        if ($existing === '' || $existing === [] || $existing === null) {
            $content[$lcKey] = $value;
            $touched++;
        } else {
            $skippedTouches++;
        }
    }
    $block['content'] = $content;
}
unset($block);

echo "==> Prefill für Home-Blocks\n";
echo "    Felder gesetzt:        " . $touched . "\n";
echo "    Übersprungen (manuell): " . $skippedTouches . "\n\n";

if ($touched === 0) {
    echo "Nichts zu tun — entweder schon gepatcht oder alle Felder manuell befüllt.\n";
    exit(0);
}

// Schreiben
$page->update([
    'builder' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
]);

echo "==> Home-Builder aktualisiert.\n";
echo "Bitte Frontend reloaden — Schatten + Section-Icons sollten jetzt da sein.\n";
