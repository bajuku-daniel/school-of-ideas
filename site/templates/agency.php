<?php
/**
 * Agentur-Detail Template — Auto-Header (Titel + Logo als Polaroid) +
 * Builder-Loop + Footer-Navigation.
 *
 * Der Header rendert sich aus den Page-Feldern (headlineInk/Accent, intro)
 * und nutzt das `Logo` Feld (= tile.png) als Bild rechts mit blauem Schatten.
 * Damit muss der Designer auf der Detail-Seite NICHTS für den Kopfbereich
 * pflegen — das Bild aus dem Übersichts-Grid wird einfach übernommen.
 */
$logo   = $page->logo()->toFile();
$next   = $page->next();
$parent = $page->parent();

// Auto-Header sichtbar wenn ein Titel da ist
$hasTitle = $page->headlineInk()->isNotEmpty() || $page->headline()->isNotEmpty() || $page->title()->isNotEmpty();
?><!doctype html>
<html lang="de">
<head>
  <?php snippet('head') ?>
</head>
<body>
  <?php snippet('subpage-nav') ?>
  <main class="subpage subpage--builder-only agency-detail">

    <?php if ($hasTitle): ?>
    <section class="headline-solo headline-solo--theme-light section--light agency-detail__auto-hero"
             data-align="left"
             data-image-layout="<?= $logo ? 'right' : 'none' ?>"
             style="--motiv-aspect: 16 / 11;">
      <div class="container">
        <div class="headline-solo__grid">
          <header class="headline-solo__head">
            <h1 class="headline-solo__title">
              <span class="ink"><?= nl2br(esc($page->headlineInk()->or($page->headline()->or($page->title())))) ?></span>
              <?php if ($page->headlineAccent()->isNotEmpty()): ?>
                <span class="accent"><?= nl2br(esc($page->headlineAccent())) ?></span>
              <?php endif ?>
            </h1>
            <?php if ($page->intro()->isNotEmpty()): ?>
              <p class="headline-solo__sub"><?= soi_html($page->intro()) ?></p>
            <?php endif ?>
          </header>

          <?php if ($logo): ?>
          <figure class="motiv motiv--shadow headline-solo__motiv"
                  data-motiv-shadow
                  data-rotate="-5"
                  data-shadow-x="40"
                  data-shadow-y="40"
                  data-shadow-rotate="8">
            <span class="motiv__shadow" aria-hidden="true"></span>
            <div class="motiv__frame"><img class="motiv__img" src="<?= esc($logo->url()) ?>"<?= soi_image_focus_attr($logo) ?> alt=""></div>
          </figure>
          <?php endif ?>
        </div>
      </div>
    </section>
    <?php endif ?>

    <?php foreach ($page->builder()->toBlocks() as $block): ?>
      <?= $block ?>
    <?php endforeach ?>

    <nav class="agency-detail__bottom-nav" aria-label="Agentur-Navigation">
      <div class="container">
        <?php if ($parent): ?>
          <a class="agency-detail__back btn-ghost" href="<?= esc($parent->url()) ?>">← Zurück zur Agenturübersicht</a>
        <?php endif ?>
        <?php if ($next): ?>
          <a class="agency-detail__next btn-mint" href="<?= esc($next->url()) ?>">Nächste Agentur →</a>
        <?php endif ?>
      </div>
    </nav>

  </main>
  <?php snippet('footer') ?>
</body>
</html>
