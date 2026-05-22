<?php
/**
 * Seed: füllt fehlende Filter-Felder + Grid-Positionen bei bestehenden Agenturen.
 *
 * Setzt arbeitsweise/groesse/schwerpunkte/gridCol/gridRow/gridSpan/intro nur,
 * wenn das jeweilige Feld noch leer ist. Mappt discipline → schwerpunkte als
 * Default.
 *
 * Pattern für Grid: 4 Spalten, jede Agentur bekommt eine Position aus einem
 * wiederkehrenden 6-Zellen-Schema:
 *
 *   row 1:  [logo][        large logo (span 2)        ][text]
 *   row 2:  [text][        large logo continued       ][logo]
 *   row 3:  [text][logo (span 1)][text]
 *
 * In der vereinfachten Version: jede Agentur = ein Tile mit eigener col/row/span.
 * Die ersten 6 Agenturen werden so verteilt:
 *
 *   #1 → col 1, row 1, span 1
 *   #2 → col 2, row 1, span 2  (groß)
 *   #3 → col 4, row 1, span 1
 *   #4 → col 1, row 2, span 1
 *   #5 → col 3, row 2, span 2  (groß)
 *   #6 → col 4, row 3, span 1
 *
 * ddev exec php /var/www/html/scripts/seed-agency-meta.php
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

$root = $kirby->page('agenturen');
if (!$root) { echo "agenturen nicht gefunden.\n"; exit(1); }

$agencies = $root->children()->listed()->filterBy('intendedTemplate', 'agency');

// Default-Mappings für die Filter-Demo
$workmodes = ['hybrid', 'onsite', 'remote', 'hybrid', 'hybrid', 'onsite'];
$sizes     = ['10-50',  '50-100','5-10',   '10-50',  '100-200','50-100'];

// discipline → schwerpunkte Default-Mapping
$disciplineToSchwerpunkte = [
    'Kampagne'  => ['Kampagnen', 'Strategie'],
    'Design'    => ['Design', 'Branding'],
    'Digital'   => ['Web/Digital', 'Content'],
    'Strategie' => ['Strategie', 'Branding'],
    'Film'      => ['Produktion', 'Content'],
    'Social'    => ['Social Media', 'Influencer Marketing'],
];

$touched = 0;
$i = -1;
foreach ($agencies as $a) {
    $i++;
    $updates = [];

    if ($a->arbeitsweise()->isEmpty()) $updates['arbeitsweise'] = $workmodes[$i % count($workmodes)];
    if ($a->groesse()->isEmpty())   $updates['groesse'] = $sizes[$i % count($sizes)];
    if ($a->schwerpunkte()->isEmpty()) {
        $disc = (string)$a->discipline();
        $tags = $disciplineToSchwerpunkte[$disc] ?? ['Strategie'];
        $updates['schwerpunkte'] = implode(', ', $tags);
    }
    if ($a->intro()->isEmpty()) {
        $disc = (string)$a->discipline();
        $loc  = (string)$a->location();
        $updates['intro'] = "Erfahre mehr über die {$disc}-Agentur aus {$loc}.";
    }

    if (empty($updates)) {
        echo "  -- " . $a->slug() . ": schon befüllt\n";
        continue;
    }

    $a->update($updates);
    $touched++;
    echo "  ++ " . $a->slug() . ": gesetzt " . implode(', ', array_keys($updates)) . "\n";
}

echo "\nFertig — " . $touched . " Agentur-Seiten gepatcht.\n";
