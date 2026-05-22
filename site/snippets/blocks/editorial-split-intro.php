<?php
/** @var \Kirby\Cms\Block $block */

$layout = (string)$block->layout()->or('1col');

// Legacy-Mapping: jedes Layout matched die bestehende SCSS-Klasse aus der alten Home.
// So funktioniert das bestehende _content.scss ohne Umbau.
$legacyMap = ['1col' => 'expect', '2col' => 'year', '3col' => 'values'];
$base      = $legacyMap[$layout] ?? 'expect';
$attrs     = soi_section_attrs($block, $base, ['--editorial-layout' => $layout]);

$imageUrl    = soi_file_url($block->image());
$shadowStyle = soi_image_shadow_style($block);
$columns     = $block->columns()->toStructure();
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
            <div class="<?= $base ?>__body"><?= soi_paragraphs($block->body(), 'text-medium') ?></div>
          <?php endif ?>
        </div>
      </header>

      <?php if ($imageUrl): ?>
      <figure class="motiv motiv--shadow <?= $base ?>__motiv"<?= soi_image_shadow_data_attrs($block) ?>
        <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
        <span class="motiv__shadow" aria-hidden="true"></span>
        <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>" alt=""></div>
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
            <?= soi_paragraphs($col->body(), 'text-medium') ?>
          </div>
        <?php endforeach ?>
      </div>
      <?php if ($imageUrl): ?>
      <figure class="motiv motiv--shadow <?= $base ?>__motiv"<?= soi_image_shadow_data_attrs($block) ?>
        <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
        <span class="motiv__shadow" aria-hidden="true"></span>
        <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>" alt=""></div>
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
            <?= soi_paragraphs($col->body(), 'text-medium') ?>
          </div>
        <?php endforeach ?>
      </div>
    <?php endif ?>
  </div>
  <?= soi_section_icon($block) ?>
</section>
