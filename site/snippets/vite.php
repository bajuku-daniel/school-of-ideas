<?php
// Cache-Buster aus mtime der Asset-Dateien. So lädt der Browser nach Build
// garantiert die frische Version (statt einer alten gecachten).
$base = kirby()->root('index') . '/assets/';
$cssV = @filemtime($base . 'main.css') ?: 0;
$jsV  = @filemtime($base . 'main.js')  ?: 0;
?>
<link rel="stylesheet" href="<?= url('assets/main.css') ?>?v=<?= $cssV ?>">
<script type="module" src="<?= url('assets/main.js') ?>?v=<?= $jsV ?>"></script>
