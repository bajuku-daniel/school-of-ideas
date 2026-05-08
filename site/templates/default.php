<?php

function soi_page_text($text): string
{
    if (is_object($text) && method_exists($text, 'value')) {
        $text = $text->value();
    }

    if (is_array($text)) {
        $text = implode("\n\n", array_map(fn ($item) => is_array($item) ? implode("\n", $item) : (string)$item, $text));
    }

    $parts = preg_split('/\R{2,}/', trim((string)$text)) ?: [];
    $html = '';

    foreach ($parts as $part) {
        if (trim($part) !== '') {
            $safe = htmlspecialchars(trim($part), ENT_QUOTES, 'UTF-8');
            $safe = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $safe);
            $safe = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $safe);
            $html .= '<p>' . nl2br($safe) . '</p>';
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

function soi_builder_spacing($section, array $spacingMap): string
{
    $top = $spacingMap[$section->spacingTop()->or('normal')->value()] ?? $spacingMap['normal'];
    $bottom = $spacingMap[$section->spacingBottom()->or('normal')->value()] ?? $spacingMap['normal'];

    if ($section->spacingTopPx()->isNotEmpty()) {
        $top = (int)$section->spacingTopPx()->value() . 'px';
    }

    if ($section->spacingBottomPx()->isNotEmpty()) {
        $bottom = (int)$section->spacingBottomPx()->value() . 'px';
    }

    $styles = [
        '--section-top: ' . $top,
        '--section-bottom: ' . $bottom,
    ];

    $styleFields = [
        'contentGapPx' => '--builder-content-gap',
        'buttonGapPx' => '--builder-button-gap',
        'buttonPaddingXPx' => '--button-pad-x',
        'buttonPaddingTopPx' => '--button-pad-top',
        'buttonPaddingBottomPx' => '--button-pad-bottom',
    ];

    foreach ($styleFields as $field => $variable) {
        if ($section->{$field}()->isNotEmpty()) {
            $styles[] = $variable . ': ' . (int)$section->{$field}()->value() . 'px';
        }
    }

    return implode('; ', $styles);
}

function soi_page_spacing($page): string
{
    $heroTop = $page->heroSpacingTopPx()->isNotEmpty() ? (int)$page->heroSpacingTopPx()->value() . 'px' : 'clamp(150px, 15vw, 240px)';
    $heroBottom = $page->heroSpacingBottomPx()->isNotEmpty() ? (int)$page->heroSpacingBottomPx()->value() . 'px' : 'clamp(72px, 10vw, 140px)';
    $introTop = $page->introSpacingTopPx()->isNotEmpty() ? (int)$page->introSpacingTopPx()->value() . 'px' : 'clamp(28px, 4vw, 56px)';

    return '--hero-top: ' . $heroTop . '; --hero-bottom: ' . $heroBottom . '; --intro-top: ' . $introTop;
}

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
  <?php snippet('subpage-nav') ?>
  <main class="subpage">
    <header class="subpage__hero" style="<?= esc(soi_page_spacing($page), 'attr') ?>">
      <div class="container">
        <p class="subpage__kicker"><?= esc($page->kicker()->or('School of Ideas')) ?></p>
        <h1 class="subpage__title">
          <span class="ink"><?= nl2br(esc($page->headlineInk()->or($page->headline()->or($page->title())))) ?></span>
          <?php if ($page->headlineAccent()->isNotEmpty()): ?><span class="accent"><?= nl2br(esc($page->headlineAccent())) ?></span><?php endif ?>
        </h1>
        <?php if ($page->intro()->isNotEmpty()): ?>
        <div class="subpage__intro"><?= soi_page_text($page->intro()->value()) ?></div>
        <?php endif ?>
      </div>
    </header>

    <?php foreach ($page->sections()->toStructure() as $section): ?>
    <?php
      $type = $section->type()->or('text')->value();
      $theme = $section->theme()->or('light')->value();
      $align = $section->align()->or('left')->value();
      $image = $section->image()->toFiles()->first();
    ?>
    <section class="builder builder--<?= esc($type, 'attr') ?> builder--<?= esc($theme, 'attr') ?> builder--<?= esc($align, 'attr') ?>" style="<?= esc(soi_builder_spacing($section, $spacingMap), 'attr') ?>">
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
            <?php if ($card->icon()->isNotEmpty()): ?><span class="builder__card-icon" aria-hidden="true"><img src="<?= url('icons/' . $card->icon()->value() . '.svg') ?>" alt=""></span><?php endif ?>
            <?php if ($card->heading()->isNotEmpty()): ?><h3><?= esc($card->heading()) ?></h3><?php endif ?>
            <?= soi_page_text($card->text()->value()) ?>
          </article>
          <?php endforeach ?>
        </div>
        <?php elseif ($type === 'faq'): ?>
        <div class="faq-list">
          <?php foreach ($section->questions()->toStructure() as $question): ?>
          <details class="faq-item">
            <summary><?= esc($question->question()) ?></summary>
            <div><?= soi_page_text($question->answer()) ?></div>
          </details>
          <?php endforeach ?>
        </div>
        <?php elseif ($type === 'cta'): ?>
        <div class="builder__text"><?= soi_page_text($section->text()->value()) ?></div>
        <?php if ($section->buttonText()->isNotEmpty()): ?><a class="btn-mint builder__button" href="<?= esc($section->buttonUrl()->or('#')) ?>"><?= esc($section->buttonText()) ?></a><?php endif ?>
        <?php elseif ($type !== 'spacer'): ?>
        <div class="builder__text"><?= soi_page_text($section->text()->value()) ?></div>
        <?php endif ?>
      </div>
    </section>
    <?php endforeach ?>
  </main>
  <?php snippet('subpage-footer') ?>
</body>
</html>
