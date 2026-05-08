<?php
function soi_agency_text($text): string
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

function soi_agency_initials(string $title): string
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

$agencies = $page->children()->listed();
$locations = $agencies->pluck('location', ',', true);
$disciplines = $agencies->pluck('discipline', ',', true);
$setups = $agencies->pluck('setup', ',', true);

sort($locations);
sort($disciplines);
sort($setups);

$heroTop = $page->heroSpacingTopPx()->isNotEmpty() ? (int)$page->heroSpacingTopPx()->value() . 'px' : 'clamp(150px, 15vw, 240px)';
$heroBottom = $page->heroSpacingBottomPx()->isNotEmpty() ? (int)$page->heroSpacingBottomPx()->value() . 'px' : 'clamp(72px, 10vw, 140px)';
$introTop = $page->introSpacingTopPx()->isNotEmpty() ? (int)$page->introSpacingTopPx()->value() . 'px' : 'clamp(28px, 4vw, 56px)';
$directoryTop = $page->directorySpacingTopPx()->isNotEmpty() ? (int)$page->directorySpacingTopPx()->value() . 'px' : 'clamp(32px, 5vw, 72px)';
$directoryBottom = $page->directorySpacingBottomPx()->isNotEmpty() ? (int)$page->directorySpacingBottomPx()->value() . 'px' : 'clamp(90px, 12vw, 180px)';
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
  <main class="subpage agencies-page">
    <header class="subpage__hero agencies-hero" style="--hero-top: <?= esc($heroTop, 'attr') ?>; --hero-bottom: <?= esc($heroBottom, 'attr') ?>; --intro-top: <?= esc($introTop, 'attr') ?>">
      <div class="container">
        <p class="subpage__kicker">Agenturen</p>
        <h1 class="subpage__title">
          <span class="ink"><?= nl2br(esc($page->headlineInk()->or($page->headline()->or('Hier lernst du, wie Kreativarbeit')))) ?></span>
          <span class="accent"><?= nl2br(esc($page->headlineAccent()->or('wirklich funktioniert.'))) ?></span>
        </h1>
        <?php if ($page->intro()->isNotEmpty()): ?><div class="subpage__intro"><?= soi_agency_text($page->intro()) ?></div><?php endif ?>
      </div>
    </header>

    <section class="agency-directory" style="--directory-top: <?= esc($directoryTop, 'attr') ?>; --directory-bottom: <?= esc($directoryBottom, 'attr') ?>" data-agency-directory>
      <div class="container">
        <div class="agency-filterbar" aria-label="Agenturen filtern">
          <label><span>Standort</span><select data-agency-filter="location"><option value="">Alle</option><?php foreach ($locations as $value): ?><option value="<?= esc($value, 'attr') ?>"><?= esc($value) ?></option><?php endforeach ?></select></label>
          <label><span>Schwerpunkt</span><select data-agency-filter="discipline"><option value="">Alle</option><?php foreach ($disciplines as $value): ?><option value="<?= esc($value, 'attr') ?>"><?= esc($value) ?></option><?php endforeach ?></select></label>
          <label><span>Setup</span><select data-agency-filter="setup"><option value="">Alle</option><?php foreach ($setups as $value): ?><option value="<?= esc($value, 'attr') ?>"><?= esc($value) ?></option><?php endforeach ?></select></label>
        </div>

        <div class="agency-grid">
          <?php foreach ($agencies as $agency): $image = $agency->logo()->toFile(); ?>
          <a class="agency-card" href="<?= $agency->url() ?>" data-location="<?= esc($agency->location(), 'attr') ?>" data-discipline="<?= esc($agency->discipline(), 'attr') ?>" data-setup="<?= esc($agency->setup(), 'attr') ?>">
            <span class="agency-card__media">
              <?php if ($image): ?><img src="<?= $image->url() ?>" alt="<?= esc($agency->title()) ?>"><?php else: ?><span><?= esc(soi_agency_initials($agency->title()->value())) ?></span><?php endif ?>
            </span>
            <span class="agency-card__body">
              <span class="agency-card__meta"><?= esc($agency->location()) ?> · <?= esc($agency->discipline()) ?></span>
              <span class="agency-card__title"><?= esc($agency->title()) ?></span>
              <span class="agency-card__text"><?= esc($agency->intro()->excerpt(150)) ?></span>
              <span class="agency-card__setup"><?= esc($agency->setup()) ?></span>
            </span>
          </a>
          <?php endforeach ?>
        </div>
        <p class="agency-empty" data-agency-empty hidden>Keine Agentur passt zu diesen Filtern.</p>
      </div>
    </section>

    <?php foreach ($page->sections()->toStructure() as $section): ?>
    <?php
      $sectionTop = $section->spacingTopPx()->isNotEmpty() ? (int)$section->spacingTopPx()->value() . 'px' : 'clamp(80px, 11vw, 160px)';
      $sectionBottom = $section->spacingBottomPx()->isNotEmpty() ? (int)$section->spacingBottomPx()->value() . 'px' : 'clamp(80px, 11vw, 160px)';
      $sectionType = $section->type()->or('text')->value();
      $sectionStyle = '--section-top: ' . $sectionTop . '; --section-bottom: ' . $sectionBottom;
      foreach (['contentGapPx' => '--builder-content-gap', 'buttonGapPx' => '--builder-button-gap', 'buttonPaddingXPx' => '--button-pad-x', 'buttonPaddingTopPx' => '--button-pad-top', 'buttonPaddingBottomPx' => '--button-pad-bottom'] as $field => $variable) {
          if ($section->{$field}()->isNotEmpty()) {
              $sectionStyle .= '; ' . $variable . ': ' . (int)$section->{$field}()->value() . 'px';
          }
      }
    ?>
    <section class="builder builder--<?= esc($sectionType, 'attr') ?>" style="<?= esc($sectionStyle, 'attr') ?>">
      <div class="container">
        <?php if ($section->heading()->isNotEmpty()): ?><h2><?= esc($section->heading()) ?></h2><?php endif ?>
        <?php if ($sectionType === 'cards'): ?>
        <div class="builder__cards"><?php foreach ($section->cards()->toStructure() as $card): ?><article class="builder__card"><?php if ($card->icon()->isNotEmpty()): ?><span class="builder__card-icon" aria-hidden="true"><img src="<?= url('icons/' . $card->icon()->value() . '.svg') ?>" alt=""></span><?php endif ?><h3><?= esc($card->heading()) ?></h3><?= soi_agency_text($card->text()) ?></article><?php endforeach ?></div>
        <?php elseif ($sectionType === 'faq'): ?>
        <div class="faq-list">
          <?php foreach ($section->questions()->toStructure() as $question): ?>
          <details class="faq-item">
            <summary><?= esc($question->question()) ?></summary>
            <div><?= soi_agency_text($question->answer()) ?></div>
          </details>
          <?php endforeach ?>
        </div>
        <?php else: ?>
        <div class="builder__text"><?= soi_agency_text($section->text()) ?></div>
        <?php if ($section->buttonText()->isNotEmpty()): ?><a class="btn-mint builder__button" href="<?= esc($section->buttonUrl()->or('#')) ?>"><?= esc($section->buttonText()) ?></a><?php endif ?>
        <?php endif ?>
      </div>
    </section>
    <?php endforeach ?>
  </main>
  <?php snippet('subpage-footer') ?>
</body>
</html>
