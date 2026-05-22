<?php
/**
 * Seed: weitere 6 Demo-Agenturen anlegen, damit das 7er-Pattern voll wird
 * (insgesamt ~12 Agenturen).
 *
 * ddev exec php /var/www/html/scripts/seed-more-agencies.php
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

$parent = $kirby->page('agenturen');
if (!$parent) { echo "agenturen nicht gefunden.\n"; exit(1); }

$newAgencies = [
    [
        'slug' => 'kollektiv-nord',
        'title' => 'Kollektiv Nord',
        'location' => 'Hamburg',
        'arbeitsweise' => 'remote',
        'groesse' => '10-30',
        'schwerpunkte' => 'Branding, Strategie',
        'discipline' => 'Strategie',
        'setup' => 'Independent',
    ],
    [
        'slug' => 'einsundzwanzig',
        'title' => 'Einsundzwanzig',
        'location' => 'Berlin',
        'arbeitsweise' => 'hybrid',
        'groesse' => '30-100',
        'schwerpunkte' => 'Social Media, Influencer Marketing',
        'discipline' => 'Social',
        'setup' => 'Spezialagentur',
    ],
    [
        'slug' => 'goldkante',
        'title' => 'Goldkante',
        'location' => 'München',
        'arbeitsweise' => 'onsite',
        'groesse' => '10-30',
        'schwerpunkte' => 'Design, Branding',
        'discipline' => 'Design',
        'setup' => 'Studio',
    ],
    [
        'slug' => 'leuchtturm-creative',
        'title' => 'Leuchtturm Creative',
        'location' => 'Hamburg',
        'arbeitsweise' => 'hybrid',
        'groesse' => '50-100',
        'schwerpunkte' => 'Kampagnen, Content',
        'discipline' => 'Kampagne',
        'setup' => 'Netzwerkagentur',
    ],
    [
        'slug' => 'gegenstrom',
        'title' => 'Gegenstrom',
        'location' => 'Düsseldorf',
        'arbeitsweise' => 'onsite',
        'groesse' => '100-200',
        'schwerpunkte' => 'Web/Digital, Content, Strategie',
        'discipline' => 'Digital',
        'setup' => 'Netzwerkagentur',
    ],
    [
        'slug' => 'unter-strom',
        'title' => 'Unter Strom',
        'location' => 'Köln',
        'arbeitsweise' => 'remote',
        'groesse' => '5-10',
        'schwerpunkte' => 'Produktion, Events',
        'discipline' => 'Film',
        'setup' => 'Independent',
    ],
];

$created = 0;
$skipped = 0;
foreach ($newAgencies as $data) {
    $slug = $data['slug'];

    if ($parent->find($slug)) {
        echo "  -- $slug: existiert schon\n";
        $skipped++;
        continue;
    }

    $page = $parent->createChild([
        'slug'     => $slug,
        'template' => 'agency',
        'content'  => [
            'title'         => $data['title'],
            'headline'      => $data['title'],
            'headlineInk'   => $data['title'],
            'intro'         => "Erfahre mehr über die {$data['discipline']}-Agentur aus {$data['location']}.",
            'location'      => $data['location'],
            'arbeitsweise'  => $data['arbeitsweise'],
            'groesse'       => $data['groesse'],
            'schwerpunkte'  => $data['schwerpunkte'],
            'discipline'    => $data['discipline'],
            'setup'         => $data['setup'],
        ],
    ])->changeStatus('listed');

    echo "  ++ $slug: angelegt\n";
    $created++;
}

echo "\nFertig — $created angelegt, $skipped übersprungen.\n";
echo "Gesamt Agenturen: " . $parent->children()->listed()->count() . "\n";
