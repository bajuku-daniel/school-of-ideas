<?php
/**
 * Style-Guide Template — Block-Referenz-Seite.
 * Versteckt im URL-Schema, baut sich rein aus Builder-Blocks zusammen.
 * Reine Dev-Übersicht für den Designer.
 */
?><!doctype html>
<html lang="de">
<head>
  <?php snippet('head', ['noindex' => true]) ?>
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
