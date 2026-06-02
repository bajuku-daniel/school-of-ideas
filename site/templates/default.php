<?php
/**
 * Default Template (Unterseiten) — komplett aus Builder-Blocks.
 * Pages bestehen jetzt nur aus Builder-Sektionen, kein hardcoded Header-Bereich
 * mehr. Den "Seitenkopf" (Headline + Bild) baut der Designer aus den vorhandenen
 * Block-Typen zusammen (z.B. headline-solo + image, oder editorial-image-text).
 */
?><!doctype html>
<html lang="de">
<head>
  <?php snippet('head') ?>
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
