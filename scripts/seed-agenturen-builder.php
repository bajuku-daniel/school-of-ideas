<?php
/**
 * Seed: agenturen-Übersicht bekommt Default-Builder mit
 *   1. agency-grid (Auto-Liste + Filter)
 *   2. conversion-section (Bottom-CTA)
 *
 * Idempotent: nur wenn builder leer ist.
 *
 * ddev exec php /var/www/html/scripts/seed-agenturen-builder.php
 */
if (php_sapi_name() !== 'cli') { echo "CLI only.\n"; exit(1); }
require __DIR__ . '/../vendor/autoload.php';
$root = dirname(__DIR__);
$kirby = new Kirby(['roots' => [
    'index'   => $root . '/public',
    'content' => $root . '/content',
    'kirby'   => $root . '/kirby',
    'site'    => $root . '/site',
]]);
$kirby->impersonate('kirby');
$page = $kirby->page('agenturen');
if (!$page) { echo "agenturen nicht gefunden.\n"; exit(1); }

if (!empty($page->builder()->value())) {
    echo "Builder schon befüllt — nichts zu tun.\n";
    exit(0);
}

$uuid = function (): string {
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    $h = bin2hex($b);
    return sprintf('%s-%s-%s-%s-%s', substr($h, 0, 8), substr($h, 8, 4), substr($h, 12, 4), substr($h, 16, 4), substr($h, 20));
};

$blocks = [
    [
        'id' => $uuid(),
        'type' => 'agency-grid',
        'isHidden' => false,
        'content' => [
            'filterLabelLocation'    => 'Standort',
            'filterLabelWorkmode'    => 'Arbeitsweise',
            'filterLabelDisciplines' => 'Schwerpunkte',
            'filterLabelSize'        => 'Agenturgröße',
            'gridCols'               => 4,
            'tileLabel'              => 'Erfahre mehr über die {schwerpunkte} Agentur aus {ort}',
        ],
    ],
    [
        'id' => $uuid(),
        'type' => 'conversion-section',
        'isHidden' => false,
        'content' => [
            'headlineInk'    => 'Kreativität hat immer Zukunft',
            'headlineAccent' => 'Und wir bilden die Talente aus, die sie gestalten.',
            'sectionTheme'   => 'light',
            'iconKey'        => 'glasses-bold',
            'iconDesktopSize' => 140,
            'iconDesktopX'   => 80,
            'iconDesktopY'   => 60,
            'iconMotion'     => 'wobble',
            'cards' => [
                ['title' => 'Du willst Kreativität zum Beruf machen?', 'buttonText' => 'Jetzt bewerben', 'buttonUrl' => '/bewerbung', 'buttonStyle' => 'mint', 'text' => ''],
                ['title' => 'Du hast eine Agentur und suchst guten Nachwuchs?', 'buttonText' => 'Jetzt mitmachen', 'buttonUrl' => '/agenturen/kontakt', 'buttonStyle' => 'mint', 'text' => ''],
            ],
        ],
    ],
];

$page->update(['builder' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
echo "agenturen Builder gesetzt (" . count($blocks) . " Blocks).\n";
