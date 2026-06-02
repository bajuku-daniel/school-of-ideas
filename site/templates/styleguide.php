<?php
/**
 * Style-Guide Template — Block-Referenz-Seite.
 * Versteckt im URL-Schema, baut sich rein aus Builder-Blocks zusammen.
 * Reine Dev-Übersicht für den Designer.
 */
?><!doctype html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Style-Guide — <?= esc($site->title()) ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" type="image/svg+xml" href="<?= url('logo/School_of_ideas_Bildlogo.svg') ?>">
  <?php snippet('vite') ?>
</head>
<body>
  <?php snippet('subpage-nav') ?>
  <main class="subpage subpage--builder-only">
    <?php foreach ($page->builder()->toBlocks() as $block): ?>
      <?= $block ?>
    <?php endforeach ?>
  </main>
  <?php snippet('footer') ?>
</body>
</html>
