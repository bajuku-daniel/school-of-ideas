<?php
/** @var \Kirby\Cms\Block $block */

$imageLayout = (string)$block->imageLayout()->or('right');
$align       = (string)$block->align()->or('left');
$imageUrl    = soi_file_url($block->image());
$aspect      = (string)$block->imageAspect()->or('landscape');
$shadowStyle = soi_image_shadow_style($block);

$aspectValue = match($aspect) {
    'square'   => '1 / 1',
    'portrait' => '3 / 4',
    default    => '16 / 11',
};

// Fallback: wenn imageLayout != "none" aber kein Bild hochgeladen → wie "none"
$hasImage = $imageUrl && in_array($imageLayout, ['right', 'left', 'below'], true);
$effectiveLayout = $hasImage ? $imageLayout : 'none';

$extraVars = ['--motiv-aspect' => $aspectValue];
$attrs = soi_section_attrs($block, 'headline-solo', $extraVars);

// SEO: Der erste Block einer Seite ist deren Hauptüberschrift → <h1>.
// Weitere headline-solo-Blöcke auf derselben Seite bleiben <h2>.
$titleTag = ($block->siblings()->first()?->id() === $block->id()) ? 'h1' : 'h2';
?>
<section<?= $attrs ?>
  data-align="<?= esc($align, 'attr') ?>"
  data-image-layout="<?= esc($effectiveLayout, 'attr') ?>">
  <div class="container">
    <div class="headline-solo__grid">
      <header class="headline-solo__head">
        <?php if ($block->kicker()->isNotEmpty()): ?>
          <p class="headline-solo__kicker"><?= esc($block->kicker()) ?></p>
        <?php endif ?>
        <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
        <<?= $titleTag ?> class="headline-solo__title">
          <?php if ($block->headlineInk()->isNotEmpty()): ?>
            <span class="ink"><?= nl2br(esc($block->headlineInk())) ?></span>
          <?php endif ?>
          <?php if ($block->headlineAccent()->isNotEmpty()): ?>
            <span class="accent"><?= nl2br(esc($block->headlineAccent())) ?></span>
          <?php endif ?>
        </<?= $titleTag ?>>
        <?php endif ?>
        <?php if ($block->sub()->isNotEmpty()): ?>
          <p class="headline-solo__sub"><?= soi_html($block->sub()) ?></p>
        <?php endif ?>
      </header>

      <?php if ($hasImage): ?>
      <figure class="motiv motiv--shadow headline-solo__motiv"<?= soi_image_shadow_data_attrs($block) ?>
        <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
        <span class="motiv__shadow" aria-hidden="true"></span>
        <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>"<?= soi_image_focus_attr($block->image()) ?> alt=""></div>
        <?= soi_section_icon_at($block, 'image') ?>
      </figure>
      <?php endif ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
