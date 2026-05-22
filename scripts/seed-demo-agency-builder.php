<?php
/**
 * Seed: füllt nova-kollektiv mit Demo-Builder-Inhalt:
 *   1. split-intro (Beschreibung)
 *   2. image-grid (3 Innenraum-Bilder)
 *   3. editorial-split-intro 1col (Kunden)
 *   4. work-showcase video (Oreo Square Cookie 1)
 *   5. work-showcase 3 images
 *   6. icon-divider (sparkles)
 *   7. editorial-split-intro 3col (Arbeitskultur)
 *   8. conversion-section
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

$page = $kirby->page('agenturen/nova-kollektiv');
if (!$page) { echo "Demo-Agentur (nova-kollektiv) nicht gefunden.\n"; exit(1); }

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
$mk = fn(string $type, array $content) => [
    'id' => $uuid(), 'type' => $type, 'isHidden' => false, 'content' => $content,
];

$blocks = [
    $mk('editorial-split-intro', [
        'layout' => '1col',
        'headlineInk' => 'Agentur',
        'headlineAccent' => 'Beschreibung',
        'sub' => 'Nova Kollektiv ist eine unabhängige Kreativ-Agentur aus Hamburg.',
        'body' => "Unsere Projekte verbinden Strategie und Idee mit handwerklich starker Umsetzung. Unsere Kunden kommen aus Lifestyle, Tech und Sport.\n\nWir machen Kampagnen für die nächste Generation Marken — mutig, klar, mit Haltung.",
        'sectionTheme' => 'light',
    ]),
    $mk('editorial-split-intro', [
        'layout' => '1col',
        'headlineInk' => 'Unsere',
        'headlineAccent' => 'Kunden',
        'body' => "AOK, Edeka, Frosta, Jägermeister, bonprix",
        'sectionTheme' => 'light',
    ]),
    $mk('editorial-split-intro', [
        'layout' => '1col',
        'headlineInk' => 'Unsere',
        'headlineAccent' => 'Arbeiten',
        'sectionTheme' => 'light',
    ]),
    $mk('work-showcase', [
        'mediaMode' => 'video',
        'videoLabel' => 'Video',
        'caseTitle' => 'Oreo Square Cookie',
        'caseTextLeft' => 'AOK 2024: Ein neues Kampagnen-Konzept mit hohem Aufmerksamkeitswert. Plattformübergreifend, in 8 Sprachen, integriert auf TikTok, Instagram, Snapchat und im AOK-CRM.',
        'caseTextRight' => 'In nur 5 Wochen Konzept bis Launch. Reichweite 12M+. CTR 5x über Branchen-Durchschnitt. Kreative Führung Nova Kollektiv.',
        'sectionTheme' => 'light',
    ]),
    $mk('work-showcase', [
        'mediaMode' => 'image3',
        'caseTitle' => 'Oreo Square Cookie',
        'caseTextLeft' => "AOK 2024: Ein neues Kampagnen-Konzept mit hohem Aufmerksamkeitswert. Plattformübergreifend, in 8 Sprachen.",
        'caseTextRight' => 'Reichweite 12M+. CTR 5x über Branchen-Durchschnitt. Kreative Führung Nova Kollektiv.',
        'sectionTheme' => 'light',
    ]),
    $mk('work-showcase', [
        'mediaMode' => 'image3',
        'caseTitle' => 'Oreo Square Cookie',
        'caseTextLeft' => 'Edeka Frühjahr 2024 — Saisonale Kommunikation für regionalen Handel.',
        'caseTextRight' => 'Crossmedialer Auftritt: Plakat, Print, Online, POS. 6-stelliger Mediabudget.',
        'sectionTheme' => 'light',
    ]),
    $mk('work-showcase', [
        'mediaMode' => 'image3',
        'caseTitle' => 'Oreo Square Cookie',
        'caseTextLeft' => 'Jägermeister Loop — kontinuierliche Always-On Kampagne.',
        'caseTextRight' => 'Markenführung über 18 Monate, Co-Kreation mit Influencer-Network.',
        'sectionTheme' => 'light',
    ]),
    $mk('icon-divider', [
        'iconKey' => 'sparkles',
        'sizeDesktop' => 110,
        'sizeTablet' => 90,
        'sizeMobile' => 70,
        'iconMotion' => 'wobble',
        'align' => 'center',
        'sectionTheme' => 'light',
    ]),
    $mk('editorial-split-intro', [
        'layout' => '3col',
        'headlineInk' => 'Unsere',
        'headlineAccent' => 'Arbeitskultur',
        'columns' => [
            [
                'lead' => 'Wie wir arbeiten',
                'body' => "Konzept-getrieben statt Tool-getrieben.\nKurze Wege, klare Entscheidungen.\nTeamarbeit über Hierarchien.\nProdukt-Quality über Quantity.",
            ],
            [
                'lead' => 'Was du lernst',
                'body' => "Wie aus Briefings große Kampagnen werden. Wie Kunden- und Kreativbedürfnisse zusammenkommen. Konkrete Projektverantwortung von Tag 1.",
            ],
            [
                'lead' => 'Was es nicht gibt',
                'body' => "Powerpoint-Schlachten.",
            ],
        ],
        'sectionTheme' => 'light',
    ]),
    $mk('conversion-section', [
        'headlineInk' => 'Du möchtest mehr über',
        'headlineAccent' => 'Nova Kollektiv erfahren?',
        'iconKey' => 'glasses-bold',
        'iconDesktopSize' => 110,
        'iconDesktopX' => 100,
        'iconDesktopY' => 60,
        'sectionTheme' => 'light',
        'cards' => [
            ['title' => 'Weitere Projekte + Arbeiten',
             'buttonText' => 'Zur Agentur-Website',
             'buttonUrl' => 'https://www.nova-kollektiv.de',
             'buttonStyle' => 'mint',
             'text' => ''],
            ['title' => 'Direkt bewerben',
             'buttonText' => 'agenturkontakt@soi.de',
             'buttonUrl' => 'mailto:agenturkontakt@soi.de',
             'buttonStyle' => 'ghost',
             'text' => ''],
        ],
    ]),
];

$page->update(['builder' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
echo "nova-kollektiv Demo-Builder gesetzt: " . count($blocks) . " Blocks.\n";
foreach ($blocks as $b) echo "  - " . $b['type'] . "\n";
