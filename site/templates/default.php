<?php

function soi_page_text($text): string
{
    $parts = preg_split('/\R{2,}/', trim((string)$text)) ?: [];
    $html = '';

    foreach ($parts as $part) {
        if (trim($part) !== '') {
            $html .= '<p>' . nl2br(htmlspecialchars(trim($part), ENT_QUOTES, 'UTF-8')) . '</p>';
        }
    }

    return $html;
}

$spacingMap = [
    'none'   => '0',
    'small'  => 'clamp(32px, 5vw, 64px)',
    'normal' => 'clamp(64px, 8vw, 112px)',
    'large'  => 'clamp(96px, 11vw, 160px)',
    'xlarge' => 'clamp(128px, 14vw, 220px)',
];

?><!doctype html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($page->title()) ?> - <?= esc($site->title()) ?></title>
  <meta name="description" content="<?= esc($page->metaDescription()->or($page->intro())->excerpt(160)) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= url('logo/School_of_ideas_Bildlogo.svg') ?>">
  <?php snippet('vite') ?>
</head>
<body>
  <main class="subpage">
    <header class="subpage__hero">
      <div class="container">
        <a class="subpage__brand" href="<?= url() ?>">
          <img src="<?= url('logo/School_of_ideas_Logo_OW.png') ?>" alt="school of ideas">
        </a>
        <h1><?= esc($page->headline()->or($page->title())) ?></h1>
        <?php if ($page->intro()->isNotEmpty()): ?>
        <div class="subpage__intro"><?= soi_page_text($page->intro()->value()) ?></div>
        <?php endif ?>
      </div>
    </header>

    <?php foreach ($page->sections()->toStructure() as $section): ?>
    <?php
      $type = $section->type()->or('text')->value();
      $top = $spacingMap[$section->spacingTop()->or('normal')->value()] ?? $spacingMap['normal'];
      $bottom = $spacingMap[$section->spacingBottom()->or('normal')->value()] ?? $spacingMap['normal'];
      $theme = $section->theme()->or('light')->value();
      $align = $section->align()->or('left')->value();
      $image = $section->image()->toFiles()->first();
    ?>
    <section class="builder builder--<?= esc($type, 'attr') ?> builder--<?= esc($theme, 'attr') ?> builder--<?= esc($align, 'attr') ?>" style="--section-top: <?= esc($top, 'attr') ?>; --section-bottom: <?= esc($bottom, 'attr') ?>">
      <div class="container">
        <?php if ($type !== 'spacer'): ?>
          <?php if ($section->kicker()->isNotEmpty()): ?><p class="builder__kicker"><?= esc($section->kicker()) ?></p><?php endif ?>
          <?php if ($section->heading()->isNotEmpty()): ?><h2><?= esc($section->heading()) ?></h2><?php endif ?>
        <?php endif ?>

        <?php if ($type === 'imageText'): ?>
        <div class="builder__split">
          <?php if ($image): ?><img src="<?= $image->url() ?>" alt="<?= esc($image->alt()) ?>"><?php endif ?>
          <div><?= soi_page_text($section->text()->value()) ?></div>
        </div>
        <?php elseif ($type === 'cards'): ?>
        <div class="builder__cards">
          <?php foreach ($section->cards()->toStructure() as $card): ?>
          <article class="builder__card">
            <?php if ($card->heading()->isNotEmpty()): ?><h3><?= esc($card->heading()) ?></h3><?php endif ?>
            <?= soi_page_text($card->text()->value()) ?>
          </article>
          <?php endforeach ?>
        </div>
        <?php elseif ($type === 'cta'): ?>
        <div class="builder__text"><?= soi_page_text($section->text()->value()) ?></div>
        <?php if ($section->buttonText()->isNotEmpty()): ?><a class="btn-mint" href="<?= esc($section->buttonUrl()->or('#')) ?>"><?= esc($section->buttonText()) ?></a><?php endif ?>
        <?php elseif ($type !== 'spacer'): ?>
        <div class="builder__text"><?= soi_page_text($section->text()->value()) ?></div>
        <?php endif ?>
      </div>
    </section>
    <?php endforeach ?>
  </main>
</body>
</html>
