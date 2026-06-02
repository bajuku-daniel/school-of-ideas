<?php
/** @var \Kirby\Cms\Block $block */

$cols = max(1, min(6, (int)$block->cols()->or(4)->value()));
$shadow = (string)$block->shadow()->or('on') === 'on';
$attrs = soi_section_attrs($block, 'image-grid', ['--image-grid-cols' => $cols]);
$items = $block->items()->toStructure();

$aspectMap = ['landscape' => '16 / 11', 'square' => '1 / 1', 'portrait' => '3 / 4'];
?>
<section<?= $attrs ?>>
  <div class="container">
    <div class="image-grid__inner">
      <?php foreach ($items as $i => $item):
        $imgUrl = soi_file_url($item->image());
        if (!$imgUrl) continue;
        $col     = max(1, (int)$item->col()->or(1)->value());
        $row     = max(1, (int)$item->row()->or(1)->value());
        $span    = max(1, (int)$item->span()->or(1)->value());
        $aspect  = (string)$item->aspect()->or('landscape');
        $caption = (string)$item->caption();
        $aspectValue = $aspectMap[$aspect] ?? '16 / 11';
        $figClass = 'image-grid__item' . ($shadow ? ' motiv motiv--shadow' : '');
      ?>
        <figure class="<?= $figClass ?>" style="--card-col:<?= $col ?>;--card-row:<?= $row ?>;--card-span:<?= $span ?>;--motiv-aspect:<?= esc($aspectValue, 'attr') ?>">
          <?php if ($shadow): ?>
            <span class="motiv__shadow" aria-hidden="true"></span>
          <?php endif ?>
          <div class="motiv__frame">
            <img class="motiv__img" src="<?= esc($imgUrl) ?>"<?= soi_image_focus_attr($img) ?> alt="<?= esc($caption) ?>">
          </div>
          <?php if ($caption !== ''): ?>
            <figcaption class="image-grid__caption"><?= soi_html($caption) ?></figcaption>
          <?php endif ?>
        </figure>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
