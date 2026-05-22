<?php
/** @var \Kirby\Cms\Block $block */

$attrs = soi_section_attrs($block, 'audience');
$items = $block->items()->toStructure();

// Bullet-Icon Resolution:
//   1. eigener Upload (bulletUpload)
//   2. Library-Key (bulletKey) → /icons/png/desktop/<key>.png
//   3. Default → leerer span, CSS rendert blauen Kreis (.card__arrow)
$bulletUrl = null;
try {
    $f = $block->bulletUpload()->toFiles()->first();
    if ($f) $bulletUrl = $f->url();
} catch (\Throwable $e) {}

if (!$bulletUrl) {
    $bk = trim((string)$block->bulletKey());
    if ($bk !== '') {
        $absPath = kirby()->root('index') . '/icons/png/desktop/' . $bk . '.png';
        if (is_file($absPath)) {
            $bulletUrl = url('icons/png/desktop/' . $bk . '.png');
        }
    }
}

// Wenn ein eigenes Icon gewählt wurde, zusätzliche Klasse damit das CSS
// nicht mehr den blauen Punkt zeichnet.
$bulletClass = 'card__arrow' . ($bulletUrl ? ' card__arrow--custom' : '');
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
          <?php if ($bulletUrl): ?>
            <span class="<?= $bulletClass ?>" aria-hidden="true"><img src="<?= esc($bulletUrl) ?>" alt=""></span>
          <?php else: ?>
            <span class="<?= $bulletClass ?>" aria-hidden="true"></span>
          <?php endif ?>
          <p class="card__text"><?= soi_html($item->text()) ?></p>
        </article>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon($block) ?>
</section>
