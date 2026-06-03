<?php
/** @var \Kirby\Cms\Block $block */

$attrs = soi_section_attrs($block, 'audience');
$items = $block->items()->toStructure();
?>
<section<?= $attrs ?>>
  <div class="container">
    <header class="audience__head">
      <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
      <h2 class="audience__lead">
        <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
        <?php if ($block->headlineAccent()->isNotEmpty()): ?>
        <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
        <?php endif ?>
      </h2>
      <?php endif ?>
      <?php if ($block->sub()->isNotEmpty()): ?>
        <p class="audience__sub"><?= soi_html($block->sub()) ?></p>
      <?php endif ?>
    </header>
    <div class="card-grid">
      <?php foreach ($items as $item):
        $col = max(1, (int)$item->col()->or(1)->value());
        $row = max(1, (int)$item->row()->or(1)->value());
        // Keine Aufzählungspunkte mehr — reiner Text, blau/schwarz färbbar
        // (--Text-- = blau). soi_html() macht selbst nl2br → daher NICHT erneut
        // ersetzen, sondern vorher mehrere Umbrüche (auch Leerzeilen) zu EINEM
        // zusammenfassen. Sonst entsteht <br /><br> (doppelter Abstand).
        $itemText = preg_replace('/[\r\n]+/', "\n", trim((string)$item->text()));
        $text = soi_html($itemText); ?>
        <article class="card card--plain" style="--card-col:<?= $col ?>;--card-row:<?= $row ?>">
          <p class="card__text"><?= $text ?></p>
        </article>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
