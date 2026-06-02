<?php
/** @var \Kirby\Cms\Block $block */

// Liest die Kindseiten der "agenturen"-Page (intendedTemplate = agency)
$root = page('agenturen');
if (!$root) {
    $root = kirby()->site()->children()->findBy('intendedTemplate', 'agenturen');
}
$agencies = $root ? $root->children()->listed()->filterBy('intendedTemplate', 'agency') : new \Kirby\Cms\Pages();

$cols = max(2, min(6, (int)$block->gridCols()->or(4)->value()));
$template = (string)$block->tileLabel()->or('Erfahre mehr über die {schwerpunkte} Agentur aus {ort}');

$attrs = soi_section_attrs($block, 'agency-grid', ['--agency-grid-cols' => $cols]);

// Filter-Optionen sammeln
$locations = []; $workmodes = []; $disciplines = []; $sizes = [];
foreach ($agencies as $a) {
    $loc = (string)$a->location();
    if ($loc !== '' && !in_array($loc, $locations, true)) $locations[] = $loc;
    $wm = (string)$a->arbeitsweise();
    if ($wm !== '' && !in_array($wm, $workmodes, true)) $workmodes[] = $wm;
    $sz = (string)$a->groesse();
    if ($sz !== '' && !in_array($sz, $sizes, true)) $sizes[] = $sz;
    foreach ($a->schwerpunkte()->split(',') as $tag) {
        $tag = trim($tag);
        if ($tag !== '' && !in_array($tag, $disciplines, true)) $disciplines[] = $tag;
    }
}
sort($locations);
$workmodeOrder = ['onsite', 'hybrid', 'remote'];
usort($workmodes, fn($a, $b) => array_search($a, $workmodeOrder) - array_search($b, $workmodeOrder));
sort($disciplines);
$sizeOrder = ['5-10', '10-30', '10-50', '30-100', '50-100', '100-200', '200+'];
usort($sizes, fn($a, $b) => array_search($a, $sizeOrder) - array_search($b, $sizeOrder));

$labels = [
    'Standort'     => $block->filterLabelLocation()->or('Standort'),
    'Arbeitsweise' => $block->filterLabelWorkmode()->or('Arbeitsweise'),
    'Schwerpunkte' => $block->filterLabelDisciplines()->or('Schwerpunkte'),
    'Agenturgröße' => $block->filterLabelSize()->or('Agenturgröße'),
];

$fillTemplate = function (string $tpl, $agency): string {
    $disc = $agency->schwerpunkte()->split(',');
    $primaryDisc = $disc ? $disc[0] : '';
    return strtr($tpl, [
        '{name}'         => (string)$agency->title(),
        '{ort}'          => (string)$agency->location(),
        '{schwerpunkte}' => $primaryDisc,
        '{groesse}'      => (string)$agency->groesse(),
        '{arbeitsweise}' => (string)$agency->arbeitsweise(),
    ]);
};

// ============================================================================
// PATTERN — pro Agentur eine Position. Fünf Formate erlaubt:
//
//   [imgCol, imgRow, 'below']            ← Bild + Text drunter (vertikales Paar)
//   [imgCol, imgRow, 'right']            ← Bild + Text rechts daneben (horizontal)
//   [imgCol, imgRow, textCol, textRow]   ← Frei wählbare Text-Position
//   [imgCol, imgRow]                     ← NUR Bild (kein Text)
//   [null, null, textCol, textRow]       ← NUR Text (kein Bild)
//
// Mix erlaubt: drunter + rechts in einem Pattern frei kombinierbar.
// 4 Spalten Grid. Pattern wiederholt sich nach $patternRows (Standard 4).
//
// Beispiel-Mix (drunter + rechts pro Zeile asymmetrisch gemischt):
//          col 1     col 2     col 3     col 4
//   row 1  [#1-img]  [#2-img]  [#2-txt]  [_____]    ← #1 drunter, #2 rechts
//   row 2  [#1-txt]  [#3-img]  [#4-img]  [#4-txt]   ← #3 drunter, #4 rechts
//   row 3  [_____]   [#3-txt]  [_____]   [_____]
//   row 4  [#5-img]  [#5-txt]  [#6-img]  [#6-txt]   ← beide rechts
// ============================================================================
$patternPositions = [
    [1, 1, 'below'],     // #1: Bild(1,1) + Text(1,2)  drunter
    [2, 1, 'right'],     // #2: Bild(2,1) + Text(3,1)  rechts daneben
    [2, 2, 'below'],     // #3: Bild(2,2) + Text(2,3)  drunter
    [3, 2, 'right'],     // #4: Bild(3,2) + Text(4,2)  rechts daneben
    [1, 4, 'right'],     // #5: Bild(1,4) + Text(2,4)  rechts daneben
    [3, 4, 'right'],     // #6: Bild(3,4) + Text(4,4)  rechts daneben
    // Weitere Varianten:
    // [2, 1, 4, 1],         // Text bei 4,1 (mit Lücke col 3)
    // [3, 1],               // nur Bild (kein Text)
    // [null, null, 1, 3],   // nur Text (kein Bild)
];
$patternRows = 4;
?>
<section<?= $attrs ?>>
  <div class="container">
    <div class="agency-grid__filters" data-filters>
      <div class="agency-grid__filter-col">
        <div class="agency-grid__filter-label"><?= esc($labels['Standort']) ?></div>
        <ul class="agency-grid__filter-list">
          <?php foreach ($locations as $opt): ?>
            <li><button type="button" data-filter="location" data-value="<?= esc($opt, 'attr') ?>"><?= esc($opt) ?></button></li>
          <?php endforeach ?>
        </ul>
      </div>
      <div class="agency-grid__filter-col">
        <div class="agency-grid__filter-label"><?= esc($labels['Arbeitsweise']) ?></div>
        <ul class="agency-grid__filter-list">
          <?php foreach ($workmodes as $opt): ?>
            <li><button type="button" data-filter="arbeitsweise" data-value="<?= esc($opt, 'attr') ?>"><?= esc($opt) ?></button></li>
          <?php endforeach ?>
        </ul>
      </div>
      <div class="agency-grid__filter-col">
        <div class="agency-grid__filter-label"><?= esc($labels['Schwerpunkte']) ?></div>
        <ul class="agency-grid__filter-list">
          <?php foreach ($disciplines as $opt): ?>
            <li><button type="button" data-filter="schwerpunkte" data-value="<?= esc($opt, 'attr') ?>"><?= esc($opt) ?></button></li>
          <?php endforeach ?>
        </ul>
      </div>
      <div class="agency-grid__filter-col">
        <div class="agency-grid__filter-label"><?= esc($labels['Agenturgröße']) ?></div>
        <ul class="agency-grid__filter-list">
          <?php foreach ($sizes as $opt): ?>
            <li><button type="button" data-filter="groesse" data-value="<?= esc($opt, 'attr') ?>"><?= esc($opt) ?></button></li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>

    <div class="agency-grid__tiles" data-agency-grid>
      <?php
        $i = -1;
        foreach ($agencies as $agency):
          $i++;
          $patternIdx = $i % count($patternPositions);
          $pos = $patternPositions[$patternIdx];
          $blockOffset = intdiv($i, count($patternPositions)) * $patternRows;

          // Flexibles Auspacken: 2-, 3- (Shortcut) oder 4-Tupel (explizit).
          $imgCol = $pos[0] ?? null;
          $imgRow = $pos[1] ?? null;
          $third  = $pos[2] ?? null;
          $fourth = $pos[3] ?? null;

          if ($third === 'below') {
            // Shortcut: Text direkt unter dem Bild (col, row+1)
            $textCol = $imgCol;
            $textRow = $imgRow !== null ? $imgRow + 1 : null;
          } elseif ($third === 'right') {
            // Shortcut: Text direkt rechts neben dem Bild (col+1, row)
            $textCol = $imgCol !== null ? $imgCol + 1 : null;
            $textRow = $imgRow;
          } else {
            // Explizite Text-Position aus pos[2], pos[3]
            $textCol = is_numeric($third) ? (int)$third : null;
            $textRow = is_numeric($fourth) ? (int)$fourth : null;
          }

          $hasImg  = $imgCol  !== null && $imgRow  !== null;
          $hasText = $textCol !== null && $textRow !== null;

          if ($hasImg)  $imgRow  += $blockOffset;
          if ($hasText) $textRow += $blockOffset;

          $logo = $agency->logo()->toFiles()->first();
          $intro = (string)$agency->intro();
          if ($intro === '') $intro = $fillTemplate($template, $agency);
          $disciplinesAttr = implode('|', $agency->schwerpunkte()->split(','));
          $url = esc($agency->url());
          $dataAttrs = ' data-location="' . esc((string)$agency->location(), 'attr') . '"'
                     . ' data-arbeitsweise="' . esc((string)$agency->arbeitsweise(), 'attr') . '"'
                     . ' data-groesse="' . esc((string)$agency->groesse(), 'attr') . '"'
                     . ' data-schwerpunkte="' . esc($disciplinesAttr, 'attr') . '"';

          // Stagger-Index für Initial-Animation: nach (row, col) sortiert
          // damit die Welle natural von oben-links nach unten-rechts läuft.
          $imgStagger  = $hasImg  ? (($imgRow  - 1) * 4 + ($imgCol  - 1)) : 0;
          $textStagger = $hasText ? (($textRow - 1) * 4 + ($textCol - 1)) : 0;
      ?>
        <?php if ($hasImg): ?>
        <a href="<?= $url ?>"
           class="agency-grid__tile agency-grid__tile--image"
           style="--card-col:<?= $imgCol ?>;--card-row:<?= $imgRow ?>;--stagger:<?= $imgStagger ?>"<?= $dataAttrs ?>>
          <?php if ($logo): ?>
            <figure class="agency-grid__tile-image motiv motiv--shadow">
              <span class="motiv__shadow" aria-hidden="true"></span>
              <div class="motiv__frame"><img class="motiv__img" src="<?= esc($logo->url()) ?>"<?= soi_image_focus_attr($logo) ?> alt=""></div>
            </figure>
          <?php else: ?>
            <figure class="agency-grid__tile-image agency-grid__placeholder" aria-hidden="true"></figure>
          <?php endif ?>
        </a>
        <?php endif ?>
        <?php if ($hasText): ?>
        <a href="<?= $url ?>"
           class="agency-grid__tile agency-grid__tile--text"
           style="--card-col:<?= $textCol ?>;--card-row:<?= $textRow ?>;--stagger:<?= $textStagger ?>"<?= $dataAttrs ?>>
          <span class="agency-grid__tile-name"><?= esc($agency->title()) ?></span>
          <span class="agency-grid__tile-arrow" aria-hidden="true">↘</span>
          <span class="agency-grid__tile-intro"><?= soi_html($intro) ?></span>
        </a>
        <?php endif ?>
      <?php endforeach ?>
    </div>
  </div>
</section>
