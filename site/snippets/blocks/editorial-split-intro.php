<?php
/** @var \Kirby\Cms\Block $block */

$layout = (string)$block->layout()->or('1col');

// Legacy-Mapping: jedes Layout matched die bestehende SCSS-Klasse aus der alten Home.
$legacyMap = ['1col' => 'expect', '2col' => 'year', '3col' => 'values'];
$base      = $legacyMap[$layout] ?? 'expect';
$attrs     = soi_section_attrs($block, $base, ['--editorial-layout' => $layout]);

$imageUrl    = soi_file_url($block->image());
$shadowStyle = soi_image_shadow_style($block);
$columns     = $block->columns()->toStructure();

// CTA-Rendering für eine Spalte (Link/Button ODER mailto mit Betreff+Text).
// Wird in 2col UND 3col genutzt, damit Buttons in beiden Layouts erscheinen.
$renderCta = function ($col) use ($base): string {
    $ctaText = trim((string)$col->ctaText());
    if ($ctaText === '') return '';

    $ctaStyle = (string)$col->ctaStyle()->or('link');
    $ctaClass = match ($ctaStyle) {
        'mint'  => 'btn-mint',
        'ghost' => 'btn-ghost',
        default => 'editorial-cta-link',
    };

    $type = (string)$col->ctaLinkType()->or('url');
    if ($type === 'mail') {
        $email  = trim((string)$col->ctaEmail());
        $params = [];
        if (($s = trim((string)$col->ctaSubject())) !== '') $params[] = 'subject=' . rawurlencode($s);
        if (($b = trim((string)$col->ctaBody()))    !== '') $params[] = 'body='    . rawurlencode($b);
        $href = 'mailto:' . $email . ($params ? '?' . implode('&', $params) : '');
        $extra = '';
    } else {
        $url   = trim((string)$col->ctaUrl());
        $href  = $url !== '' ? $url : '#';
        $extra = str_starts_with($url, 'http') ? ' target="_blank" rel="noopener"' : '';
    }

    return '<a class="' . $ctaClass . ' ' . $base . '__block-cta" href="'
         . esc($href, 'attr') . '"' . $extra . '>' . esc($ctaText) . '</a>';
};
?>
<section<?= $attrs ?> data-layout="<?= esc($layout, 'attr') ?>">
  <div class="container">
    <?php if ($layout === '1col'): ?>
      <header class="<?= $base ?>__head">
        <h2 class="<?= $base ?>__lead">
          <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
          <?php if ($block->headlineAccent()->isNotEmpty()): ?>
            <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
          <?php endif ?>
        </h2>
        <div class="<?= $base ?>__copy">
          <?php if ($block->sub()->isNotEmpty()): ?>
            <p class="<?= $base ?>__sub"><?= soi_html($block->sub()) ?></p>
          <?php endif ?>
          <?php if ($block->body()->isNotEmpty()): ?>
            <div class="<?= $base ?>__body"><?= soi_paragraphs($block->body()) ?></div>
          <?php endif ?>
        </div>
      </header>

      <?php if ($imageUrl): ?>
      <figure class="motiv motiv--shadow <?= $base ?>__motiv"<?= soi_image_shadow_data_attrs($block) ?>
        <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
        <span class="motiv__shadow" aria-hidden="true"></span>
        <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>"<?= soi_image_focus_attr($block->image()) ?> alt=""></div>
        <?= soi_section_icon_at($block, 'image') ?>
      </figure>
      <?php endif ?>
    <?php endif ?>

    <?php if ($layout === '2col'): ?>
      <h2 class="<?= $base ?>__lead">
        <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
        <?php if ($block->headlineAccent()->isNotEmpty()): ?>
          <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
        <?php endif ?>
      </h2>
      <div class="<?= $base ?>__copy">
        <?php foreach ($columns as $i => $col): $n = $i + 1; ?>
          <div class="<?= $base ?>__block <?= $base ?>__block--lead-<?= $n ?>">
            <?php if ($col->lead()->isNotEmpty()): ?>
              <p class="<?= $base ?>__block-lead"><?= soi_html($col->lead()) ?></p>
            <?php endif ?>
          </div>
          <div class="<?= $base ?>__block <?= $base ?>__block--body-<?= $n ?>">
            <?= soi_paragraphs($col->body()) ?>
            <?= $renderCta($col) ?>
          </div>
        <?php endforeach ?>
      </div>
      <?php if ($imageUrl): ?>
      <figure class="motiv motiv--shadow <?= $base ?>__motiv"<?= soi_image_shadow_data_attrs($block) ?>
        <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
        <span class="motiv__shadow" aria-hidden="true"></span>
        <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>"<?= soi_image_focus_attr($block->image()) ?> alt=""></div>
        <?= soi_section_icon_at($block, 'image') ?>
      </figure>
      <?php endif ?>
    <?php endif ?>

    <?php if ($layout === '3col'): ?>
      <h2 class="<?= $base ?>__lead">
        <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
        <?php if ($block->headlineAccent()->isNotEmpty()): ?>
          <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
        <?php endif ?>
      </h2>
      <div class="<?= $base ?>__grid">
        <?php foreach ($columns as $col): ?>
          <div class="<?= $base ?>__col">
            <?php if ($col->lead()->isNotEmpty()): ?>
              <p class="<?= $base ?>__col-lead"><?= soi_html($col->lead()) ?></p>
            <?php endif ?>
            <?= soi_paragraphs($col->body()) ?>
            <?= $renderCta($col) ?>
          </div>
        <?php endforeach ?>
      </div>
    <?php endif ?>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
