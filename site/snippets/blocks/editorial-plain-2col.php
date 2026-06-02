<?php
/** @var \Kirby\Cms\Block $block */

$attrs = soi_section_attrs($block, 'editorial-plain-2col');
?>
<section<?= $attrs ?>>
  <div class="container">
    <?php if ($block->kicker()->isNotEmpty()): ?>
      <p class="editorial-plain-2col__kicker"><?= esc($block->kicker()) ?></p>
    <?php endif ?>
    <div class="editorial-plain-2col__grid">
      <div class="editorial-plain-2col__col">
        <?= soi_paragraphs($block->column1()) ?>
      </div>
      <div class="editorial-plain-2col__col">
        <?= soi_paragraphs($block->column2()) ?>
      </div>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
