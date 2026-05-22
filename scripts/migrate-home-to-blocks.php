<?php
/**
 * Migration: Home-Page von hardcoded Sections → Builder mit Blocks.
 *
 * Liest die alten Felder (hero*, intro*, manifest, expect*, year*, values*,
 * audience*, outcome*, final*, *-spacing) und schreibt sie in einen JSON-Builder
 * (Kirby `blocks` Feld) zurück.
 *
 * Sicher: legt vorher eine Backup-Kopie der home.txt an.
 *
 * Ausführung (im Repo-Root):
 *   ddev exec php /var/www/html/scripts/migrate-home-to-blocks.php
 *   ddev exec php /var/www/html/scripts/migrate-home-to-blocks.php --dry-run
 */

if (php_sapi_name() !== 'cli') {
    echo "Bitte nur per CLI ausführen.\n";
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
$dryRun = in_array('--dry-run', $argv, true);

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
if (!$page) {
    echo "Home-Page nicht gefunden.\n";
    exit(1);
}

echo "==> Home Block-Migration\n";
echo "    Kirby-User: kirby (impersonated)\n";
echo "    Mode: " . ($dryRun ? 'DRY-RUN (kein write)' : 'WRITE') . "\n\n";

// Backup
$contentFile = $page->root() . '/home.txt';
if (!$dryRun && file_exists($contentFile)) {
    $backup = $page->root() . '/home.txt.bak-' . date('Ymd-His');
    copy($contentFile, $backup);
    echo "    Backup: $backup\n\n";
}

/**
 * Helper: Field-Value oder leer als String.
 */
$fv = fn(string $field) => trim((string)$page->{$field}());

/**
 * Helper: Datei-UUID aus File-Field. Kirby-Blocks erwarten die rohe UUID
 * im "files:1" Field als Array mit einem Element.
 */
$fileUuid = function(string $field) use ($page): array {
    $file = $page->{$field}()->toFiles()->first();
    if (!$file) return [];
    return [$file->uuid()->toString()];
};

/**
 * Helper: structure als plain array.
 */
$asStruct = function(string $field) use ($page): array {
    $items = $page->{$field}()->toStructure();
    if ($items->isEmpty()) return [];
    return array_map(function ($item) {
        $arr = $item->toArray();
        // entferne "id" / Kirby-interne keys; behalte nur die Content-Keys
        unset($arr['id']);
        return $arr;
    }, $items->values());
};

/**
 * Spacing-Map: aus alten Field-Prefixen Top/Bottom × Mobile/Tablet/Desktop ziehen.
 * z.B. introTop, introTopTablet, introTopMobile → spacingTop[Desktop|Tablet|Mobile]
 */
$spacing = function(string $prefix) use ($page): array {
    $get = fn(string $f) => $page->{$f}()->isNotEmpty() ? (int)$page->{$f}()->value() : null;
    return array_filter([
        'spacingTopDesktop'    => $get($prefix . 'Top'),
        'spacingTopTablet'     => $get($prefix . 'TopTablet'),
        'spacingTopMobile'     => $get($prefix . 'TopMobile'),
        'spacingBottomDesktop' => $get($prefix . 'Bottom'),
        'spacingBottomTablet'  => $get($prefix . 'BottomTablet'),
        'spacingBottomMobile'  => $get($prefix . 'BottomMobile'),
    ], fn($v) => $v !== null);
};

// Helper: macht UUID (v4-ish ausreichend für Block-IDs)
$uuid = function(): string {
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    $h = bin2hex($b);
    return sprintf('%s-%s-%s-%s-%s', substr($h, 0, 8), substr($h, 8, 4), substr($h, 12, 4), substr($h, 16, 4), substr($h, 20));
};

$mkBlock = function(string $type, array $content) use ($uuid): array {
    return [
        'id'       => $uuid(),
        'type'     => $type,
        'isHidden' => false,
        'content'  => $content,
    ];
};

$blocks = [];

// ============================================================================
// 1) HERO
// ============================================================================
$blocks[] = $mkBlock('hero', [
    'heroLine1'        => $fv('heroLine1'),
    'heroLine2Prefix'  => $fv('heroLine2Prefix'),
    'heroIcon'         => $fv('heroIcon'),
    'heroLine2Suffix'  => $fv('heroLine2Suffix'),
    'heroPoster'       => $fileUuid('heroPoster'),
    'heroVideo'        => $fileUuid('heroVideo'),
    'heroVideoMobile'  => $fileUuid('heroVideoMobile'),
    'anchorId'         => 'hero',
    'sectionTheme'     => 'light',
]);

// ============================================================================
// 2) SPLIT-INTRO (.intro)
// ============================================================================
$blocks[] = $mkBlock('split-intro', array_merge([
    'headlineInk'      => $fv('introHeadlineInk'),
    'headlineAccent'   => $fv('introHeadlineAccent'),
    'sub'              => $fv('introSub'),
    'body'             => $fv('introBody'),
    'image'            => $fileUuid('introImage'),
    'ctaPrimaryText'   => 'Jetzt bewerben',
    'ctaPrimaryUrl'    => '#contact',
    'ctaSecondaryText' => 'Selbsttest starten',
    'ctaSecondaryUrl'  => '#contact',
    'anchorId'         => 'about',
    'sectionTheme'     => 'light',
], $spacing('intro')));

// ============================================================================
// 3) HIGHLIGHT-TEXT (.manifest)
// ============================================================================
// Icon-Pool: bestehende Library als lokalen Pool kopieren (alle Icons des Manifest)
$globalIcons = $asStruct('iconLibrary');
$iconPool = array_map(function ($icon) {
    return [
        'key'  => $icon['key'] ?? '',
        'file' => $icon['iconfile'] ?? [],
    ];
}, $globalIcons);

$blocks[] = $mkBlock('highlight-text', array_merge([
    'paragraphs'   => $asStruct('manifest'),
    'iconPool'     => $iconPool,
    'sectionTheme' => 'light',
], $spacing('manifest')));

// ============================================================================
// 4) EDITORIAL-SPLIT-INTRO 1col (.expect)
// ============================================================================
$blocks[] = $mkBlock('editorial-split-intro', array_merge([
    'layout'         => '1col',
    'headlineInk'    => $fv('expectHeadlineInk'),
    'headlineAccent' => $fv('expectHeadlineAccent'),
    'sub'            => $fv('expectSub'),
    'body'           => $fv('expectBody'),
    'image'          => $fileUuid('expectImage'),
    'anchorId'       => 'program',
    'sectionTheme'   => 'light',
], $spacing('expect')));

// ============================================================================
// 5) EDITORIAL-SPLIT-INTRO 2col (.year)
// ============================================================================
$blocks[] = $mkBlock('editorial-split-intro', array_merge([
    'layout'         => '2col',
    'headlineInk'    => $fv('yearHeadlineInk'),
    'headlineAccent' => $fv('yearHeadlineAccent'),
    'columns'        => $asStruct('yearBlocks'),
    'image'          => $fileUuid('yearImage'),
    'anchorId'       => 'year',
    'sectionTheme'   => 'light',
], $spacing('year')));

// ============================================================================
// 6) EDITORIAL-SPLIT-INTRO 3col (.values)
// ============================================================================
$blocks[] = $mkBlock('editorial-split-intro', array_merge([
    'layout'         => '3col',
    'headlineInk'    => $fv('valuesHeadlineInk'),
    'headlineAccent' => $fv('valuesHeadlineAccent'),
    'columns'        => $asStruct('valuesColumns'),
    'anchorId'       => 'values',
    'sectionTheme'   => 'light',
], $spacing('values')));

// ============================================================================
// 7) EDITORIAL-BULLET-LIST mit subline (.audience)
// ============================================================================
$blocks[] = $mkBlock('editorial-bullet-list', array_merge([
    'headlineInk'    => $fv('audienceHeadlineInk'),
    'headlineAccent' => $fv('audienceHeadlineAccent'),
    'sub'            => $fv('audienceSub'),
    'items'          => $asStruct('audienceCards'),
    'anchorId'       => 'audience',
    'sectionTheme'   => 'light',
], $spacing('audience')));

// ============================================================================
// 8) EDITORIAL-BULLET-LIST ohne subline (.outcome)
// ============================================================================
$blocks[] = $mkBlock('editorial-bullet-list', array_merge([
    'headlineInk'    => $fv('outcomeHeadlineInk'),
    'headlineAccent' => $fv('outcomeHeadlineAccent'),
    'sub'            => '',
    'items'          => $asStruct('outcomeCards'),
    'anchorId'       => 'outcome',
    'sectionTheme'   => 'light',
], $spacing('outcome')));

// ============================================================================
// 9) CONVERSION-SECTION (.cta-final)
// ============================================================================
$blocks[] = $mkBlock('conversion-section', array_merge([
    'headlineInk'    => $fv('finalHeadlineInk'),
    'headlineAccent' => $fv('finalHeadlineAccent'),
    'image'          => $fileUuid('finalImage'),
    'cards'          => $asStruct('finalCards'),
    'anchorId'       => 'contact',
    'sectionTheme'   => 'light',
], $spacing('final')));

// ============================================================================
// Übersicht
// ============================================================================
echo "    Erzeugte Blocks (" . count($blocks) . "):\n";
foreach ($blocks as $i => $b) {
    $label = $b['content']['headlineInk'] ?? $b['content']['heroLine1'] ?? '(ohne Headline)';
    echo "      " . ($i + 1) . ". " . str_pad($b['type'], 26) . " — " . str_replace("\n", ' / ', $label) . "\n";
}
echo "\n";

if ($dryRun) {
    echo "==> DRY-RUN — nichts geschrieben.\n";
    exit(0);
}

// ============================================================================
// Schreiben via Kirby update()
// ============================================================================
try {
    $page = $page->update([
        'builder' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
    echo "==> Builder-Feld geschrieben. " . count($blocks) . " Blocks angelegt.\n";
} catch (\Throwable $e) {
    echo "FEHLER beim Update: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nFertig. Bitte Panel öffnen und prüfen: /panel/pages/home\n";
