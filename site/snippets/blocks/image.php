<?php
/** @var \Kirby\Cms\Block $block */

$aspect       = (string)$block->aspect()->or('landscape');
$aspectCustom = trim((string)$block->aspectCustom());
$align        = (string)$block->align()->or('center');
$imageUrl     = soi_file_url($block->image());
$caption      = (string)$block->caption();

$aspectValue = match($aspect) {
    'square'   => '1 / 1',
    'portrait' => '3 / 4',
    'custom'   => $aspectCustom !== '' ? $aspectCustom : '16 / 11',
    default    => '16 / 11',
};

$extraVars = ['--motiv-aspect' => $aspectValue];
$shadowStyle = soi_image_shadow_style($block);
if ($shadowStyle !== '') {
    foreach (explode(';', $shadowStyle) as $pair) {
        if (str_contains($pair, ':')) {
            [$k, $v] = explode(':', $pair, 2);
            $extraVars[trim($k)] = trim($v);
        }
    }
}

// Designer-Overrides für Bildbreite (pro BP) + Offset
foreach (['Mobile', 'Tablet', 'Desktop'] as $bp) {
    try {
        $w = $block->{'width' . $bp}();
        if ($w->isNotEmpty()) {
            $extraVars['--image-width-' . strtolower($bp)] = (int)$w->value() . '%';
        }
    } catch (\Throwable $e) {}
}
foreach (['offsetX' => '--image-offset-x', 'offsetY' => '--image-offset-y'] as $field => $cssVar) {
    try {
        $v = $block->{$field}();
        if ($v->isNotEmpty()) {
            $extraVars[$cssVar] = (int)$v->value() . 'px';
        }
    } catch (\Throwable $e) {}
}

$attrs = soi_section_attrs($block, 'image-block', $extraVars);
?>
<section<?= $attrs ?> data-align="<?= esc($align, 'attr') ?>">
  <div class="container">
    <?php if ($imageUrl): ?>
    <figure class="motiv motiv--shadow image-block__motiv image-block__motiv--<?= esc($align, 'attr') ?>"<?= soi_image_shadow_data_attrs($block) ?>>
      <span class="motiv__shadow" aria-hidden="true"></span>
      <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>" alt=""></div>
      <?php if ($caption !== ''): ?>
      <figcaption class="image-block__caption"><?= soi_html($caption) ?></figcaption>
      <?php endif ?>
      <?= soi_section_icon_at($block, 'image') ?>
    </figure>
    <?php endif ?>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
