<?php
/** @var \Kirby\Cms\Block $block */

$line1    = (string)$block->heroLine1();
$prefix   = (string)$block->heroLine2Prefix();
$suffix   = (string)$block->heroLine2Suffix();
$icon     = trim((string)$block->heroIcon());
$photoUrl = soi_file_url($block->photo());
$aspect   = (string)$block->aspect()->or('landscape');

$aspectValue = match($aspect) {
    'square'   => '1 / 1',
    'portrait' => '3 / 4',
    default    => '16 / 11',
};

// Hero-Icon Resolution (gleich wie hero.php)
$iconHtml = '';
$iconUrl  = null;
try {
    $f = $block->heroIconUpload()->toFiles()->first();
    if ($f) $iconUrl = $f->url();
} catch (\Throwable $e) {}
if (!$iconUrl && $icon !== '') {
    $absPath = kirby()->root('index') . '/icons/png/desktop/' . $icon . '.png';
    if (is_file($absPath)) {
        $iconUrl = url('icons/png/desktop/' . $icon . '.png');
    }
}
if ($iconUrl) {
    // NBSP statt Space: Icon klebt am vorhergehenden Wort, kein Orphan-Umbruch.
    $iconHtml = '&nbsp;<span class="site-icon star-inline" aria-hidden="true"><img class="site-icon__img" src="' . esc($iconUrl) . '" alt=""></span> ';
}

$mode = (string)$block->displayMode()->or('foto');

// Gemeinsamer Headline-Block (Zeile 1 + Zeile 2 mit Inline-Icon).
ob_start(); ?>
      <?php if ($line1 !== ''): ?>
        <span class="line"><?= soi_html($line1) ?></span>
      <?php endif ?>
      <?php if ($prefix !== '' || $suffix !== '' || $iconHtml !== ''): ?>
        <span class="line"><?= soi_html($prefix) ?><?= $iconHtml ?><?= soi_html($suffix) ?></span>
      <?php endif ?>
<?php $titleInner = ob_get_clean();
?>
<?php if ($mode === 'vollbild'): ?>
<?php // Vollbild: identische .hero-Struktur wie der Video-Hero, nur <img> statt
      // <video>. Erbt damit Layout (16:9, Text unten links, weiß), Overlay,
      // Mint-Leiste und — über die .hero-Klasse — den Nav-Glas-Trigger.
  $attrs = soi_section_attrs($block, 'hero');
?>
<section<?= $attrs ?>>
  <?php if ($photoUrl): ?>
  <img class="hero__image" src="<?= esc($photoUrl) ?>"<?= soi_image_focus_attr($block->photo()) ?> alt="">
  <?php endif ?>
  <div class="hero__overlay"></div>
  <div class="hero__content">
    <h1 class="hero__title"><?= $titleInner ?></h1>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
<div class="hero__mint" aria-hidden="true"></div>
<?php else: ?>
<?php
  $shadowStyle = soi_image_shadow_style($block);
  $attrs = soi_section_attrs($block, 'hero-foto', ['--motiv-aspect' => $aspectValue]);
?>
<section<?= $attrs ?>>
  <div class="container">
    <div class="hero-foto__grid">
      <div class="hero-foto__copy">
        <h1 class="hero-foto__title"><?= $titleInner ?></h1>
        <?php if ($block->sub()->isNotEmpty()): ?>
          <p class="hero-foto__sub"><?= soi_html($block->sub()) ?></p>
        <?php endif ?>
      </div>
      <?php if ($photoUrl): ?>
      <figure class="hero-foto__media motiv motiv--shadow"<?= soi_image_shadow_data_attrs($block) ?>
        <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
        <span class="motiv__shadow" aria-hidden="true"></span>
        <div class="motiv__frame"><img class="motiv__img" src="<?= esc($photoUrl) ?>"<?= soi_image_focus_attr($block->photo()) ?> alt=""></div>
        <?= soi_section_icon_at($block, 'image') ?>
      </figure>
      <?php endif ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
<?php endif ?>
