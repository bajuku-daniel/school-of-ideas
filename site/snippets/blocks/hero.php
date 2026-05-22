<?php
/** @var \Kirby\Cms\Block $block */

$poster      = soi_file_url($block->heroPoster());
$videoDt     = soi_file_url($block->heroVideo());
$videoMob    = soi_file_url($block->heroVideoMobile());
$line1       = (string)$block->heroLine1();
$prefix      = (string)$block->heroLine2Prefix();
$suffix      = (string)$block->heroLine2Suffix();
$icon        = trim((string)$block->heroIcon());
$attrs       = soi_section_attrs($block, 'hero');

// Hero-Icon Resolution:
//   1. heroIconUpload — eigene Datei (gewinnt)
//   2. heroIcon       — Bibliotheks-Key → /icons/png/desktop/<key>.png
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
    $iconHtml = ' <span class="site-icon star-inline" aria-hidden="true"><img class="site-icon__img" src="' . esc($iconUrl) . '" alt=""></span> ';
}
?>
<section<?= $attrs ?>>
  <video
    class="hero__video"
    autoplay muted loop playsinline webkit-playsinline preload="metadata"
    <?php if ($poster): ?>poster="<?= esc($poster) ?>"<?php endif ?>
    <?php if ($videoMob): ?>data-mobile-src="<?= esc($videoMob) ?>"<?php endif ?>
    <?php if ($videoDt): ?>data-desktop-src="<?= esc($videoDt) ?>"<?php endif ?>
  >
    <?php if ($videoMob): ?>
    <source src="<?= esc($videoMob) ?>" type="video/mp4" media="(max-width: 768px)">
    <?php endif ?>
    <?php if ($videoDt): ?>
    <source src="<?= esc($videoDt) ?>" type="video/mp4">
    <?php endif ?>
  </video>
  <div class="hero__overlay"></div>
  <div class="hero__content">
    <h1 class="hero__title">
      <span class="line"><?= soi_html($line1) ?></span>
      <?php if ($prefix !== '' || $suffix !== '' || $iconHtml !== ''): ?>
      <span class="line"><?= soi_html($prefix) ?><?= $iconHtml ?><?= soi_html($suffix) ?></span>
      <?php endif ?>
    </h1>
  </div>
  <?= soi_section_icon($block) ?>
</section>
<div class="hero__mint" aria-hidden="true"></div>
