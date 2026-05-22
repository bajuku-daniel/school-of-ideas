<?php
/** @var \Kirby\Cms\Block $block */

$iconKey = trim((string)$block->iconKey());
$align   = (string)$block->align()->or('center');

// Custom Upload schlägt Library
$url = null;
try {
    $f = $block->iconUpload()->toFiles()->first();
    if ($f) $url = $f->url();
} catch (\Throwable $e) {}
if (!$url && $iconKey !== '') {
    $abs = kirby()->root('index') . '/icons/png/desktop/' . $iconKey . '.png';
    if (is_file($abs)) $url = url('icons/png/desktop/' . $iconKey . '.png');
}
if (!$url) return; // ohne Icon → kein Block rendern

// Sizes als CSS-Vars
$sizes = [];
foreach (['mobile' => 64, 'tablet' => 90, 'desktop' => 120] as $bp => $default) {
    $field = 'size' . ucfirst($bp);
    try {
        $v = $block->{$field}();
        if ($v->isNotEmpty()) $sizes[$bp] = (int)$v->value() . 'px';
        else $sizes[$bp] = $default . 'px';
    } catch (\Throwable $e) { $sizes[$bp] = $default . 'px'; }
}

$motion = (string)$block->iconMotion()->or('wobble');
$motionName = $motion === 'none' ? 'none' : ($motion === 'glide' ? 'floatGlide' : 'floatWobble');

$extraVars = [
    '--icon-divider-size-mobile'  => $sizes['mobile'],
    '--icon-divider-size-tablet'  => $sizes['tablet'],
    '--icon-divider-size-desktop' => $sizes['desktop'],
    '--icon-motion-name'          => $motionName,
];

$attrs = soi_section_attrs($block, 'icon-divider', $extraVars);
?>
<section<?= $attrs ?> data-align="<?= esc($align, 'attr') ?>">
  <div class="container">
    <span class="icon-divider__icon icon-divider__icon--<?= esc($align, 'attr') ?>" aria-hidden="true" data-motion="<?= esc($motion, 'attr') ?>">
      <img src="<?= esc($url) ?>" alt="">
    </span>
  </div>
</section>
