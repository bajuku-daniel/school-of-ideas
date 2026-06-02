<?php
/** @var \Kirby\Cms\Block $block */

$attrs       = soi_section_attrs($block, 'cta-final');
$imageUrl    = soi_file_url($block->image());
$shadowStyle = soi_image_shadow_style($block);
$cards       = $block->cards()->toStructure();
?>
<section<?= $attrs ?>>
  <div class="container">
    <?php if ($imageUrl): ?>
    <figure class="motiv motiv--shadow cta-final__motiv"<?= soi_image_shadow_data_attrs($block) ?>
      <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
      <span class="motiv__shadow" aria-hidden="true"></span>
      <div class="motiv__frame"><img class="motiv__img" src="<?= esc($imageUrl) ?>" alt=""></div>
      <?= soi_section_icon_at($block, 'image') ?>
    </figure>
    <?php endif ?>

    <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
    <h2 class="cta-final__title">
      <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
      <?php if ($block->headlineAccent()->isNotEmpty()): ?>
      <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
      <?php endif ?>
    </h2>
    <?php endif ?>

    <div class="cta-final__cards">
      <?php foreach ($cards as $card):
        $style = (string)$card->buttonStyle()->or('mint');
        $btnText = (string)$card->buttonText();
        $btnUrl  = (string)$card->buttonUrl()->or('#');
        $title   = (string)$card->title();
        $text    = (string)$card->text();
      ?>
        <article class="cta-final__card">
          <span class="card__arrow" aria-hidden="true"></span>
          <?php if ($title !== ''): ?>
            <h3 class="cta-final__card-title"><?= soi_html($title) ?></h3>
          <?php endif ?>
          <?php if ($style !== 'none' && $btnText !== ''): ?>
            <a class="<?= $style === 'ghost' ? 'btn-ghost' : 'btn-mint' ?>" href="<?= esc($btnUrl) ?>"><?= esc($btnText) ?></a>
          <?php elseif ($text !== ''): ?>
            <p class="cta-final__card-text"><?= soi_html($text) ?></p>
          <?php endif ?>
        </article>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
