<?php
/** @var \Kirby\Cms\Block $block */

$attrs = soi_section_attrs($block, 'audience');
$items = $block->items()->toStructure();

// Bullet-Icon Resolution:
//   1. Eigener Upload (bulletUpload) → schlägt Bibliothek
//   2. Library-Key (bulletKey) → <picture> mit 3 BP-Strichstärken
//   3. Default → leerer span, CSS rendert blauen Kreis (.card__arrow)
$uploadUrl = null;
try {
    $f = $block->bulletUpload()->toFiles()->first();
    if ($f) $uploadUrl = $f->url();
} catch (\Throwable $e) {}

$bulletKey  = trim((string)$block->bulletKey());
$bulletHtml = soi_library_picture($bulletKey, $uploadUrl, '', '');

// Klasse setzt CSS-Logik: --custom überspringt den blauen Punkt
$bulletClass = 'card__arrow' . ($bulletHtml !== '' ? ' card__arrow--custom' : '');
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
        $row = max(1, (int)$item->row()->or(1)->value()); ?>
        <article class="card" style="--card-col:<?= $col ?>;--card-row:<?= $row ?>">
          <span class="<?= $bulletClass ?>" aria-hidden="true"><?= $bulletHtml ?></span>
          <p class="card__text"><?= soi_html($item->text()) ?></p>
        </article>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
