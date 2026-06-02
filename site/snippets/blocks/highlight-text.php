<?php
/** @var \Kirby\Cms\Block $block */

$attrs    = soi_section_attrs($block, 'manifest');
$pool     = soi_block_icon_pool($block);
$paragraphs = $block->paragraphs()->toStructure();
?>
<section<?= $attrs ?>>
  <div class="container">
    <div class="manifest__inner">
      <?php foreach ($paragraphs as $p): ?>
        <p class="manifest__text"><?= soi_icon_text((string)$p->text(), $pool, 'manifest__icon') ?></p>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
