<?php
/**
 * Agentur-Detail Template — Hero mit Stammdaten + Builder-Loop + Footer.
 * Stammdaten (Logo, Location, Schwerpunkte, Setup) bleiben außerhalb des Builders,
 * weil sie für das Übersichts-Grid und die Filter gelesen werden.
 */
$logo = $page->logo()->toFile();
$next = $page->next();
$parent = $page->parent();
?><!doctype html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($page->title()) ?> — <?= esc($site->title()) ?></title>
  <meta name="description" content="<?= esc($page->metaDescription()->or($page->intro())->excerpt(160)) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= url('logo/School_of_ideas_Bildlogo.svg') ?>">
  <?php snippet('vite') ?>
</head>
<body>
  <?php snippet('subpage-nav') ?>
  <main class="subpage agency-detail">

    <header class="agency-detail__hero">
      <div class="container">
        <?php if ($page->headlineInk()->isNotEmpty() || $page->headline()->isNotEmpty() || $page->title()->isNotEmpty()): ?>
        <h1 class="agency-detail__title">
          <span class="ink"><?= nl2br(esc($page->headlineInk()->or($page->headline()->or($page->title())))) ?></span>
          <?php if ($page->headlineAccent()->isNotEmpty()): ?>
            <span class="accent"><?= nl2br(esc($page->headlineAccent())) ?></span>
          <?php endif ?>
        </h1>
        <?php endif ?>
        <?php if ($page->intro()->isNotEmpty()): ?>
          <div class="agency-detail__intro"><?= soi_paragraphs($page->intro()) ?></div>
        <?php endif ?>
      </div>
    </header>

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
  <?php snippet('subpage-footer') ?>
</body>
</html>
