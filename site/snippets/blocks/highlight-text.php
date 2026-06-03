<?php
/** @var \Kirby\Cms\Block $block */

$pool       = soi_block_icon_pool($block);
$paragraphs = $block->paragraphs()->toStructure();

// Text → HTML mit festen Umbrüchen: jede Zeile (Enter) wird zu <br>.
// Icons werden vorher per soi_icon_text aufgelöst, danach die Zeilenumbrüche.
$lines = function (string $raw) use ($pool): string {
    $raw = trim($raw);
    if ($raw === '') return '';
    $html = soi_icon_text($raw, $pool, 'manifest__icon');
    return preg_replace("/\r\n|\r|\n/", '<br>', $html);
};

// Fit-Knöpfe → CSS-Variablen (Spaltenbreite) bzw. data-Attribute (Font-Grenzen).
$px = fn ($field) => $block->{$field}()->isNotEmpty() ? (int)$block->{$field}()->value() : null;

$widthDesktop = $px('fitWidthDesktop');
$widthMobile  = $px('fitWidthMobile');

$extraVars = [
    '--manifest-fit-width-desktop' => $widthDesktop !== null ? $widthDesktop . 'px' : null,
    '--manifest-fit-width-mobile'  => $widthMobile  !== null ? $widthMobile  . 'px' : null,
];

$attrs = soi_section_attrs($block, 'manifest', $extraVars);

$fontMaxDesktop = $px('fitFontMaxDesktop');
$fontMaxMobile  = $px('fitFontMaxMobile');
$fontMin        = $px('fitFontMin');
?>
<section<?= $attrs ?> data-manifest-fit<?php
  if ($fontMaxDesktop !== null) echo ' data-fit-max-desktop="' . $fontMaxDesktop . '"';
  if ($fontMaxMobile  !== null) echo ' data-fit-max-mobile="'  . $fontMaxMobile  . '"';
  if ($fontMin        !== null) echo ' data-fit-min="'         . $fontMin        . '"';
?>>
  <div class="container">
    <div class="manifest__inner">
      <?php foreach ($paragraphs as $p):
        $desk = $lines((string)$p->text());
        $mob  = $lines((string)$p->textMobile());
        if ($desk === '' && $mob === '') continue;
      ?>
        <p class="manifest__text">
          <?php if ($mob !== '' && $mob !== $desk): ?>
            <span class="manifest__bp manifest__bp--desktop"><?= $desk !== '' ? $desk : $mob ?></span>
            <span class="manifest__bp manifest__bp--mobile"><?= $mob ?></span>
          <?php else: ?>
            <span class="manifest__bp manifest__bp--all"><?= $desk !== '' ? $desk : $mob ?></span>
          <?php endif ?>
        </p>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
