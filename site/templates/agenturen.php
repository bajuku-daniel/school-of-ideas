<?php
/**
 * Agenturen-Übersicht — Subpage-Hero + Builder-Loop + Footer.
 * Filter + Grid sind ein Builder-Block (agency-grid), füllt sich automatisch
 * aus den Agentur-Unterseiten.
 */
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
  <main class="subpage agencies-page">
    <?php if ($page->headlineInk()->isNotEmpty() || $page->kicker()->isNotEmpty() || $page->intro()->isNotEmpty()): ?>
    <header class="subpage__hero">
      <div class="container">
        <?php if ($page->kicker()->isNotEmpty()): ?>
          <p class="subpage__kicker"><?= esc($page->kicker()) ?></p>
        <?php endif ?>
        <h1 class="subpage__title">
          <span class="ink"><?= nl2br(esc($page->headlineInk()->or($page->title()))) ?></span>
          <?php if ($page->headlineAccent()->isNotEmpty()): ?>
            <span class="accent"><?= nl2br(esc($page->headlineAccent())) ?></span>
          <?php endif ?>
        </h1>
        <?php if ($page->intro()->isNotEmpty()): ?>
          <div class="subpage__intro"><?= soi_paragraphs($page->intro()) ?></div>
        <?php endif ?>
      </div>
    </header>
    <?php endif ?>

    <?php foreach ($page->builder()->toBlocks() as $block): ?>
      <?= $block ?>
    <?php endforeach ?>
  </main>
  <?php snippet('subpage-footer') ?>
</body>
</html>
