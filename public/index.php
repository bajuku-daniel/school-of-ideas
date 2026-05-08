<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);

echo (new Kirby([
    'roots' => [
        'index'   => __DIR__,
        'content' => $root . '/content',
        'kirby'   => $root . '/kirby',
        'site'    => $root . '/site',
    ],
]))->render();
