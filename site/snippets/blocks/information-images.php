<?php
/** @var \Kirby\Cms\Block $block */

$layout = (string)$block->layout()->or('grid');
$cols   = max(2, min(6, (int)$block->cols()->or(3)->value()));
$aspect = (string)$block->aspect()->or('portrait');
$cards  = $block->cards()->toStructure();

$aspectValue = match($aspect) {
    'square'    => '1 / 1',
    'landscape' => '4 / 3',
    default     => '3 / 4',
};

$attrs = soi_section_attrs($block, 'information-images', [
    '--info-images-cols'   => $cols,
    '--info-images-aspect' => $aspectValue,
]);
?>
<section<?= $attrs ?> data-layout="<?= esc($layout, 'attr') ?>">
  <div class="container">
    <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
      <header class="information-images__head">
        <h2 class="information-images__title">
          <?php if ($block->headlineInk()->isNotEmpty()): ?>
            <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
          <?php endif ?>
          <?php if ($block->headlineAccent()->isNotEmpty()): ?>
            <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
          <?php endif ?>
        </h2>
        <?php if ($block->sub()->isNotEmpty()): ?>
          <p class="information-images__sub"><?= soi_html($block->sub()) ?></p>
        <?php endif ?>
      </header>
    <?php endif ?>

    <?php if ($layout === 'organic'): ?>
    <?php /* ----- Organische Anordnung: Bild- und Text-Kachel als getrennte
              Grid-Zellen, pro Person frei über Spalte/Zeile/Text-Position
              platziert (wie beim Agentur-Grid, aber im Panel pflegbar). ----- */ ?>
    <div class="information-images__grid information-images__grid--organic" data-reveal-tiles>
      <?php
        $i = -1;
        foreach ($cards as $card):
          $i++;
          $imageUrl = soi_file_url($card->image());
          $name     = (string)$card->name();
          $position = (string)$card->position();
          $url      = trim((string)$card->url());
          $textPos  = (string)$card->textPos()->or('below');

          // Spalte/Zeile aus dem Panel — leer = automatisch der Reihe nach.
          $imgCol = (int)$card->col()->value();
          $imgRow = (int)$card->row()->value();
          if ($imgCol < 1) $imgCol = ($i % $cols) + 1;
          if ($imgRow < 1) $imgRow = intdiv($i, $cols) * 2 + 1;

          // Text-Zelle relativ zum Bild.
          $textCol = $imgCol;
          $textRow = $imgRow;
          if     ($textPos === 'right') { $textCol = $imgCol + 1; }
          elseif ($textPos === 'left')  { $textCol = max(1, $imgCol - 1); }
          else                          { $textRow = $imgRow + 1; } // below

          $hasImg  = $imageUrl !== null && $imageUrl !== '';
          $hasText = $name !== '' || $position !== '';

          // Stagger für die Reveal-Welle (oben-links → unten-rechts).
          $imgStagger  = ($imgRow  - 1) * $cols + ($imgCol  - 1);
          $textStagger = ($textRow - 1) * $cols + ($textCol - 1);

          $tag      = $url !== '' ? 'a' : 'div';
          $hrefAttr = $url !== '' ? ' href="' . esc($url) . '"' . (str_starts_with($url, 'http') ? ' target="_blank" rel="noopener"' : '') : '';
      ?>
        <?php if ($hasImg): ?>
        <<?= $tag ?> class="information-images__tile information-images__tile--image"
           style="--card-col:<?= $imgCol ?>;--card-row:<?= $imgRow ?>;--stagger:<?= $imgStagger ?>"<?= $hrefAttr ?>>
          <figure class="information-images__media">
            <img src="<?= esc($imageUrl) ?>"<?= soi_image_focus_attr($card->image()) ?> alt="<?= esc($name) ?>">
          </figure>
        </<?= $tag ?>>
        <?php endif ?>
        <?php if ($hasText): ?>
        <<?= $tag ?> class="information-images__tile information-images__tile--text"
           style="--card-col:<?= $textCol ?>;--card-row:<?= $textRow ?>;--stagger:<?= $textStagger ?>"<?= $hrefAttr ?>>
          <?php if ($name !== ''): ?>
            <p class="information-images__name"><?= esc($name) ?></p>
          <?php endif ?>
          <?php if ($position !== ''): ?>
            <p class="information-images__position"><?= esc($position) ?></p>
          <?php endif ?>
        </<?= $tag ?>>
        <?php endif ?>
      <?php endforeach ?>
    </div>

    <?php else: ?>
    <?php /* ----- Gleichmäßiges Raster (Standard, unverändert) ----- */ ?>
    <div class="information-images__grid">
      <?php foreach ($cards as $card):
        $imageUrl = soi_file_url($card->image());
        $name     = (string)$card->name();
        $position = (string)$card->position();
        $url      = trim((string)$card->url());
        $tag      = $url !== '' ? 'a' : 'div';
        $hrefAttr = $url !== '' ? ' href="' . esc($url) . '"' . (str_starts_with($url, 'http') ? ' target="_blank" rel="noopener"' : '') : '';
      ?>
        <<?= $tag ?> class="information-images__card"<?= $hrefAttr ?>>
          <figure class="information-images__media">
            <?php if ($imageUrl): ?>
              <img src="<?= esc($imageUrl) ?>"<?= soi_image_focus_attr($card->image()) ?> alt="<?= esc($name) ?>">
            <?php else: ?>
              <span class="information-images__placeholder" aria-hidden="true"></span>
            <?php endif ?>
          </figure>
          <?php if ($name !== ''): ?>
            <p class="information-images__name"><?= esc($name) ?></p>
          <?php endif ?>
          <?php if ($position !== ''): ?>
            <p class="information-images__position"><?= esc($position) ?></p>
          <?php endif ?>
        </<?= $tag ?>>
      <?php endforeach ?>
    </div>
    <?php endif ?>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
