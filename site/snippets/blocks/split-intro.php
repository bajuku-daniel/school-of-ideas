<?php
/** @var \Kirby\Cms\Block $block */

// Optionale Designer-Overrides für den Abstand Bild → Headline (pro BP).
$motivVars = [];
foreach (['Mobile', 'Tablet', 'Desktop'] as $bp) {
    try {
        $f = $block->{'motivSpacingTop' . $bp}();
        if ($f->isNotEmpty()) {
            $motivVars['--motiv-spacing-top-' . strtolower($bp)] = (int)$f->value() . 'px';
        }
    } catch (\Throwable $e) {}
}

$attrs       = soi_section_attrs($block, 'intro', $motivVars);
$imageUrl    = soi_file_url($block->image());
$shadowStyle = soi_image_shadow_style($block);

// v2 Section-Icon rendert sich an der vom User gewählten Position
// (Bezug = 'image' → in figure, 'section' → direkt in section).
?>
<section<?= $attrs ?>>
  <div class="container">
    <div class="intro__grid">
      <div class="intro__col intro__col--left">
        <h2 class="intro__lead">
          <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
          <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
        </h2>
        <?php if ($imageUrl): ?>
        <figure class="motiv motiv--shadow"<?= soi_image_shadow_data_attrs($block) ?>
          <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
          <span class="motiv__shadow" aria-hidden="true"></span>
          <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>" alt=""></div>
          <?= soi_section_icon_at($block, 'image') ?>
        </figure>
        <?php endif ?>
      </div>
      <div class="intro__col intro__col--right">
        <?php if ($block->sub()->isNotEmpty()): ?>
        <p class="intro__sub"><?= soi_html($block->sub()) ?></p>
        <?php endif ?>
        <?php if ($block->body()->isNotEmpty()): ?>
        <div class="intro__body"><?= soi_paragraphs($block->body(), 'text-medium') ?></div>
        <?php endif ?>
        <?php
          $p1Text = (string)$block->ctaPrimaryText();
          $p1Url  = (string)$block->ctaPrimaryUrl();
          $p2Text = (string)$block->ctaSecondaryText();
          $p2Url  = (string)$block->ctaSecondaryUrl();
        ?>
        <?php if ($p1Text !== '' || $p2Text !== ''): ?>
        <div class="intro__ctas">
          <?php if ($p1Text !== ''): ?><a class="btn-mint" href="<?= esc($p1Url ?: '#') ?>"><?= esc($p1Text) ?></a><?php endif ?>
          <?php if ($p2Text !== ''): ?><a class="btn-ghost" href="<?= esc($p2Url ?: '#') ?>"><?= esc($p2Text) ?></a><?php endif ?>
        </div>
        <?php endif ?>
      </div>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
