<?php
/** @var \Kirby\Cms\Block $block */

$align = (string)$block->align()->or('left');
$attrs = soi_section_attrs($block, 'headline-solo');
?>
<section<?= $attrs ?> data-align="<?= esc($align, 'attr') ?>">
  <div class="container">
    <header class="headline-solo__head">
      <?php if ($block->kicker()->isNotEmpty()): ?>
        <p class="headline-solo__kicker"><?= esc($block->kicker()) ?></p>
      <?php endif ?>
      <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
      <h2 class="headline-solo__title">
        <?php if ($block->headlineInk()->isNotEmpty()): ?>
          <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
        <?php endif ?>
        <?php if ($block->headlineAccent()->isNotEmpty()): ?>
          <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
        <?php endif ?>
      </h2>
      <?php endif ?>
      <?php if ($block->sub()->isNotEmpty()): ?>
        <p class="headline-solo__sub"><?= soi_html($block->sub()) ?></p>
      <?php endif ?>
    </header>
  </div>
  <?= soi_section_icon_v2($block) ?>
</section>
