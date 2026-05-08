<?php
function soi_detail_text($text): string
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
function soi_detail_initials(string $title): string
{
    $words = preg_split('/\s+/', trim($title)) ?: [];
    $initials = '';
    foreach ($words as $word) {
        $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        if (mb_strlen($initials) >= 2) {
            break;
        }
    }
    return $initials ?: 'SO';
}
$image = $page->logo()->toFile();
$heroTop = $page->heroSpacingTopPx()->isNotEmpty() ? (int)$page->heroSpacingTopPx()->value() . 'px' : 'clamp(150px, 15vw, 240px)';
$heroBottom = $page->heroSpacingBottomPx()->isNotEmpty() ? (int)$page->heroSpacingBottomPx()->value() . 'px' : 'clamp(72px, 10vw, 130px)';
$introTop = $page->introSpacingTopPx()->isNotEmpty() ? (int)$page->introSpacingTopPx()->value() . 'px' : 'clamp(28px, 4vw, 56px)';
$contentTop = $page->contentSpacingTopPx()->isNotEmpty() ? (int)$page->contentSpacingTopPx()->value() . 'px' : 'clamp(80px, 11vw, 160px)';
$contentBottom = $page->contentSpacingBottomPx()->isNotEmpty() ? (int)$page->contentSpacingBottomPx()->value() . 'px' : 'clamp(80px, 11vw, 160px)';
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
  <main class="subpage agency-detail">
    <header class="agency-detail__hero" style="--hero-top: <?= esc($heroTop, 'attr') ?>; --hero-bottom: <?= esc($heroBottom, 'attr') ?>; --intro-top: <?= esc($introTop, 'attr') ?>">
      <div class="container agency-detail__grid">
        <div>
          <p class="subpage__kicker"><?= esc($page->location()) ?> · <?= esc($page->discipline()) ?></p>
          <h1 class="subpage__title">
            <span class="ink"><?= esc($page->headlineInk()->or($page->headline()->or($page->title()))) ?></span>
            <?php if ($page->headlineAccent()->isNotEmpty()): ?><span class="accent"><?= esc($page->headlineAccent()) ?></span><?php endif ?>
          </h1>
          <?php if ($page->intro()->isNotEmpty()): ?><div class="subpage__intro"><?= soi_detail_text($page->intro()) ?></div><?php endif ?>
          <p class="agency-detail__back"><a href="<?= $page->parent()->url() ?>">← Zurück zu allen Agenturen</a></p>
        </div>
        <figure class="agency-detail__media"><?php if ($image): ?><img src="<?= $image->url() ?>" alt="<?= esc($page->title()) ?>"><?php else: ?><span><?= esc(soi_detail_initials($page->title()->value())) ?></span><?php endif ?></figure>
      </div>
    </header>
    <section class="agency-detail__content" style="--content-top: <?= esc($contentTop, 'attr') ?>; --content-bottom: <?= esc($contentBottom, 'attr') ?>">
      <div class="container">
        <div class="agency-detail__body"><?= soi_detail_text($page->body()->or($page->intro())) ?></div>
        <div class="agency-detail__facts">
          <article><strong>Standort</strong><span><?= esc($page->location()) ?></span></article>
          <article><strong>Schwerpunkt</strong><span><?= esc($page->discipline()) ?></span></article>
          <article><strong>Setup</strong><span><?= esc($page->setup()) ?></span></article>
          <?php if ($page->website()->isNotEmpty()): $website = $page->website()->value(); ?>
          <article><strong>Website</strong><a href="<?= esc($website) ?>" target="_blank" rel="noopener"><?= esc(parse_url($website, PHP_URL_HOST) ?: $website) ?></a></article>
          <?php endif ?>
        </div>
        <?php if ($page->cards()->isNotEmpty()): ?>
        <div class="builder__cards agency-detail__cards"><?php foreach ($page->cards()->toStructure() as $card): ?><article class="builder__card"><?php if ($card->icon()->isNotEmpty()): ?><span class="builder__card-icon" aria-hidden="true"><img src="<?= url('icons/' . $card->icon()->value() . '.svg') ?>" alt=""></span><?php endif ?><h3><?= esc($card->heading()) ?></h3><?= soi_detail_text($card->text()) ?></article><?php endforeach ?></div>
        <?php endif ?>
      </div>
    </section>
  </main>
  <?php snippet('subpage-footer') ?>
</body>
</html>
