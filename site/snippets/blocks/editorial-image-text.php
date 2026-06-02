<?php
/** @var \Kirby\Cms\Block $block */

$side       = (string)$block->imageSide()->or('left');
$aspect     = (string)$block->aspect()->or('landscape');
$imageUrl   = soi_file_url($block->image());
$shadowStyle = soi_image_shadow_style($block);

$aspectValue = match($aspect) {
    'square'   => '1 / 1',
    'portrait' => '3 / 4',
    default    => '16 / 11',
};

$extraVars = ['--motiv-aspect' => $aspectValue];
$attrs = soi_section_attrs($block, 'editorial-image-text', $extraVars);

$ctaText = (string)$block->ctaText();
$ctaUrl  = (string)$block->ctaUrl()->or('#');
?>
<section<?= $attrs ?> data-image-side="<?= esc($side, 'attr') ?>">
  <div class="container">
    <div class="editorial-image-text__grid">
      <?php if ($imageUrl): ?>
      <figure class="editorial-image-text__media motiv motiv--shadow"<?= soi_image_shadow_data_attrs($block) ?>
        <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
        <span class="motiv__shadow" aria-hidden="true"></span>
        <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>"<?= soi_image_focus_attr($block->image()) ?> alt=""></div>
        <?= soi_section_icon_at($block, 'image') ?>
      </figure>
      <?php endif ?>
      <div class="editorial-image-text__copy">
        <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
        <h2 class="editorial-image-text__title">
          <?php if ($block->headlineInk()->isNotEmpty()): ?>
            <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
          <?php endif ?>
          <?php if ($block->headlineAccent()->isNotEmpty()): ?>
            <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
          <?php endif ?>
        </h2>
        <?php endif ?>
        <?php if ($block->body()->isNotEmpty()): ?>
          <div class="editorial-image-text__body"><?= soi_paragraphs($block->body()) ?></div>
        <?php endif ?>
        <?php if ($ctaText !== ''): ?>
          <a class="btn-mint editorial-image-text__cta" href="<?= esc($ctaUrl) ?>"><?= esc($ctaText) ?></a>
        <?php endif ?>
      </div>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
