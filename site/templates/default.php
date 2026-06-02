<?php
/**
 * Default Template (Unterseiten) — komplett aus Builder-Blocks.
 * Pages bestehen jetzt nur aus Builder-Sektionen, kein hardcoded Header-Bereich
 * mehr. Den "Seitenkopf" (Headline + Bild) baut Björn aus den vorhandenen
 * Block-Typen zusammen (z.B. headline-solo + image, oder editorial-image-text).
 */
?><!doctype html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($page->title()) ?> — <?= esc($site->title()) ?></title>
  <meta name="description" content="<?= esc($page->metaDescription()->or($page->title())) ?>">
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
