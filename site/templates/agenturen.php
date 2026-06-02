<?php
/**
 * Agenturen-Übersicht — komplett aus Builder-Blocks.
 * Filter + Grid sind ein Builder-Block (agency-grid), füllt sich automatisch
 * aus den Agentur-Unterseiten.
 */
?><!doctype html>
<html lang="de">
<head>
  <?php snippet('head') ?>
</head>
<body>
  <?php snippet('subpage-nav') ?>
  <main class="subpage subpage--builder-only agencies-page">
    <?php foreach ($page->builder()->toBlocks() as $block): ?>
      <?= $block ?>
    <?php endforeach ?>
  </main>
  <?php snippet('footer') ?>
</body>
</html>
