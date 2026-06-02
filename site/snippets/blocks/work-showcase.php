<?php
/** @var \Kirby\Cms\Block $block */

$mode  = (string)$block->mediaMode()->or('image2');
$attrs = soi_section_attrs($block, 'work-showcase', ['--work-mode' => $mode]);
$shadowStyle = soi_image_shadow_style($block);
?>
<section<?= $attrs ?> data-mode="<?= esc($mode, 'attr') ?>">
  <div class="container">

    <?php if ($mode === 'video'):
      $videoUrl  = soi_file_url($block->video());
      $posterUrl = soi_file_url($block->videoPoster());
      $videoLabel = (string)$block->videoLabel()->or('Video'); ?>
      <figure class="work-showcase__media work-showcase__media--video">
        <?php if ($videoUrl): ?>
        <video src="<?= esc($videoUrl) ?>" <?php if ($posterUrl): ?>poster="<?= esc($posterUrl) ?>"<?php endif ?>
               controls playsinline preload="metadata"></video>
        <?php elseif ($posterUrl): ?>
        <img src="<?= esc($posterUrl) ?>" alt="">
        <?php else: ?>
        <div class="work-showcase__placeholder" aria-hidden="true"></div>
        <?php endif ?>
        <?php if ($videoLabel !== ''): ?>
          <span class="work-showcase__video-label"><?= esc($videoLabel) ?></span>
        <?php endif ?>
      </figure>
    <?php endif ?>

    <?php if ($mode === 'image2'):
      $images = $block->images2()->toFiles(); ?>
      <div class="work-showcase__media work-showcase__media--2col">
        <?php foreach ($images as $img): ?>
          <figure class="work-showcase__tile motiv motiv--shadow"<?= soi_image_shadow_data_attrs($block) ?>
            <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
            <span class="motiv__shadow" aria-hidden="true"></span>
            <div class="motiv__frame"><img class="motiv__img" src="<?= esc($img->url()) ?>" alt=""></div>
          </figure>
        <?php endforeach ?>
        <?php for ($i = $images->count(); $i < 2; $i++): ?>
          <figure class="work-showcase__tile work-showcase__placeholder" aria-hidden="true"></figure>
        <?php endfor ?>
      </div>
    <?php endif ?>

    <?php if ($mode === 'image3'):
      $images = $block->images3()->toFiles(); ?>
      <div class="work-showcase__media work-showcase__media--3col">
        <?php foreach ($images as $img): ?>
          <figure class="work-showcase__tile motiv motiv--shadow"<?= soi_image_shadow_data_attrs($block) ?>
            <?php if ($shadowStyle): ?>style="<?= esc($shadowStyle, 'attr') ?>"<?php endif ?>>
            <span class="motiv__shadow" aria-hidden="true"></span>
            <div class="motiv__frame"><img class="motiv__img" src="<?= esc($img->url()) ?>" alt=""></div>
          </figure>
        <?php endforeach ?>
        <?php for ($i = $images->count(); $i < 3; $i++): ?>
          <figure class="work-showcase__tile work-showcase__placeholder" aria-hidden="true"></figure>
        <?php endfor ?>
      </div>
    <?php endif ?>

    <?php $title = (string)$block->caseTitle();
          $left  = (string)$block->caseTextLeft();
          $right = (string)$block->caseTextRight();
          if ($title !== '' || $left !== '' || $right !== ''): ?>
    <div class="work-showcase__copy">
      <?php if ($title !== ''): ?>
        <h3 class="work-showcase__title"><?= soi_html($title) ?></h3>
      <?php endif ?>
      <?php if ($left !== '' || $right !== ''): ?>
      <div class="work-showcase__text">
        <div class="work-showcase__col"><?= soi_paragraphs($left) ?></div>
        <div class="work-showcase__col"><?= soi_paragraphs($right) ?></div>
      </div>
      <?php endif ?>
    </div>
    <?php endif ?>

  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
