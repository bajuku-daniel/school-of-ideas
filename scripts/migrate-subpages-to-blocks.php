<?php
/**
 * Migration: Unterseiten von alter "sections" structure → Builder-Blocks.
 *
 * Für jede Unterseite mit `sections:` Inhalt → konvertiert in Builder.
 * Mapping pro Sektionstyp:
 *   text       → editorial-split-intro (1col)
 *   cards      → editorial-bullet-list  (items aus cards.heading + cards.text)
 *   faq        → question-answers       (eine Default-Gruppe)
 *   cta        → conversion-section
 *   spacer     → kein Block (übersprungen)
 *
 * Idempotent: nur wenn `builder` leer ist UND `sections` nicht.
 * Backup pro Seite wird erzeugt.
 *
 * Ausführung:
 *   ddev exec php /var/www/html/scripts/migrate-subpages-to-blocks.php
 *   ddev exec php /var/www/html/scripts/migrate-subpages-to-blocks.php --dry-run
 */

if (php_sapi_name() !== 'cli') { echo "CLI only.\n"; exit(1); }
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

$uuid = function (): string {
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    $h = bin2hex($b);
    return sprintf('%s-%s-%s-%s-%s', substr($h, 0, 8), substr($h, 8, 4), substr($h, 12, 4), substr($h, 16, 4), substr($h, 20));
};
$mkBlock = fn(string $type, array $content) => [
    'id' => $uuid(),
    'type' => $type,
    'isHidden' => false,
    'content' => $content,
];

echo "==> Unterseiten-Migration\n";
echo "    Mode: " . ($dryRun ? 'DRY-RUN' : 'WRITE') . "\n\n";

$pages = $kirby->site()->index()->filterBy('intendedTemplate', 'default');
foreach ($pages as $page) {
    $sections = $page->sections()->toStructure();
    if ($sections->isEmpty()) continue;

    $existingBuilder = $page->builder()->value();
    if (!empty($existingBuilder)) {
        echo "  -- " . $page->slug() . ": Builder schon befüllt → überspringen\n";
        continue;
    }

    $blocks = [];
    // Helper: Field → safe string (handle array values from structure-fields)
    $f = function ($field) {
        if (!is_object($field)) return '';
        $v = $field->value();
        if (is_array($v)) return '';
        return is_string($v) ? $v : (string)$v;
    };
    foreach ($sections as $sec) {
        $type   = (string)$sec->type()->or('text');
        $theme  = (string)$sec->theme()->or('light');
        $kicker = $f($sec->kicker());
        $head   = $f($sec->heading());
        $text   = $f($sec->text());
        $base = [
            'sectionTheme' => $theme,
        ];

        switch ($type) {
            case 'cards':
                // cards-structure → editorial-bullet-list (items = heading + text)
                $items = [];
                foreach ($sec->cards()->toStructure() as $card) {
                    $combined = trim($f($card->heading()));
                    $body = trim($f($card->text()));
                    if ($body !== '') {
                        $combined .= "\n" . $body;
                    }
                    $items[] = ['text' => $combined, 'col' => count($items) % 4 + 1, 'row' => 1];
                }
                $blocks[] = $mkBlock('editorial-bullet-list', $base + [
                    'headlineInk' => $head,
                    'sub'         => $text,
                    'items'       => $items,
                ]);
                break;

            case 'faq':
                $questions = [];
                foreach ($sec->questions()->toStructure() as $q) {
                    $questions[] = [
                        'question' => $f($q->question()),
                        'answer'   => $f($q->answer()),
                    ];
                }
                $blocks[] = $mkBlock('question-answers', $base + [
                    'headlineInk' => $head,
                    'groups' => [[
                        'title' => '',
                        'questions' => $questions,
                    ]],
                ]);
                break;

            case 'cta':
                $btnText = (string)$sec->buttonText();
                $btnUrl  = (string)$sec->buttonUrl()->or('#');
                $cards = [];
                if ($btnText !== '') {
                    $cards[] = [
                        'title' => $head,
                        'buttonText' => $btnText,
                        'buttonUrl'  => $btnUrl,
                        'buttonStyle' => 'mint',
                        'text' => '',
                    ];
                }
                $blocks[] = $mkBlock('conversion-section', $base + [
                    'headlineInk' => $head !== '' ? $head : 'Bereit?',
                    'cards' => $cards,
                ]);
                break;

            case 'spacer':
                // Nichts rendern. Spacing wird ggf. an den Nachbarblöcken eingestellt.
                continue 2;

            case 'text':
            case 'imageText':
            default:
                $blocks[] = $mkBlock('editorial-split-intro', $base + [
                    'layout' => '1col',
                    'headlineInk' => $head,
                    'sub'  => '',
                    'body' => $text,
                ]);
                break;
        }
    }

    if (empty($blocks)) {
        echo "  -- " . $page->slug() . ": keine konvertierbaren Sections\n";
        continue;
    }

    echo "  ++ " . $page->slug() . ": " . count($blocks) . " Block(s)";
    foreach ($blocks as $b) echo " [" . $b['type'] . "]";
    echo "\n";

    if (!$dryRun) {
        // Backup
        $contentFile = $page->root() . '/default.txt';
        if (file_exists($contentFile)) {
            copy($contentFile, $page->root() . '/default.txt.bak-' . date('Ymd-His'));
        }
        $page->update([
            'builder' => json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}

echo "\nFertig.\n";
