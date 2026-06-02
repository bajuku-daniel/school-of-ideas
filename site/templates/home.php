<?php
/**
 * Home Template — render via Builder-Blocks.
 *
 * Alle Section-Logik lebt in /site/snippets/blocks/*.php.
 * Hier nur: Page-Chrome (Head, Nav, Builder-Loop, Footer).
 */

$navItems = $page->navItems()->toStructure();
$navItemsArr = $navItems->isEmpty() ? [
    ['label' => 'About',     'url' => url('about')],
    ['label' => 'Programm',  'url' => url('programm')],
    ['label' => 'Agenturen', 'url' => url('agenturen')],
    ['label' => 'Kontakt',   'url' => url('kontakt')],
    ['label' => 'FAQ',       'url' => url('faq')],
] : array_map(fn($i) => ['label' => (string)$i->label(), 'url' => (string)$i->url()], $navItems->values());

$footerLinks = $page->footerLinks()->toStructure();
$footerLinksArr = $footerLinks->isEmpty() ? $navItemsArr
    : array_map(fn($i) => ['label' => (string)$i->label(), 'url' => (string)$i->url()], $footerLinks->values());

$legalLinks = $page->legalLinks()->toStructure();
$legalLinksArr = $legalLinks->isEmpty() ? [
    ['label' => 'Impressum',   'url' => url('impressum')],
    ['label' => 'Datenschutz', 'url' => url('datenschutz')],
] : array_map(fn($i) => ['label' => (string)$i->label(), 'url' => (string)$i->url()], $legalLinks->values());

$ctaLabel = soi_text($page, 'ctaLabel', 'Bewerbung');
$ctaUrl   = soi_text($page, 'ctaUrl', '#contact');
?><!doctype html>
<html lang="de">
<head>
  <?php snippet('head') ?>
</head>
<body>
  <header class="nav" id="siteNav" data-glass="false">
    <div class="nav__inner">
      <div class="nav__left">
        <a class="nav__brand" href="<?= url() ?>" aria-label="school of ideas">
          <span class="nav__brand-stack">
            <img class="nav__brand-text" src="<?= url('logo/soi_logo_wordmark.svg') ?>" alt="school of ideas">
            <img class="nav__cloud" src="<?= url('logo/soi_logo_cloud.svg') ?>" alt="" aria-hidden="true">
          </span>
        </a>
      </div>
      <div class="nav__right">
        <nav aria-label="Primär">
          <ul class="nav__list">
            <?php foreach ($navItemsArr as $item): ?>
            <li><a class="nav__link" href="<?= esc($item['url']) ?>"><?= esc($item['label']) ?></a></li>
            <?php endforeach ?>
          </ul>
        </nav>
        <a href="<?= esc($ctaUrl) ?>" class="nav__cta"><?= esc($ctaLabel) ?></a>
        <button class="nav__burger" id="navBurger" aria-label="Menü öffnen" aria-expanded="false" aria-controls="mobileMenu" type="button"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>

  <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
    <nav class="mobile-menu__nav" aria-label="Mobile navigation">
      <ul class="mobile-menu__list">
        <?php foreach ($navItemsArr as $item): ?>
        <li><a href="<?= esc($item['url']) ?>" class="mobile-menu__link"><span><?= esc($item['label']) ?></span></a></li>
        <?php endforeach ?>
        <li><a href="<?= esc($ctaUrl) ?>" class="mobile-menu__link mobile-menu__link--cta"><span><?= esc($ctaLabel) ?></span></a></li>
      </ul>
    </nav>
  </div>

  <main>
    <?php
      // Builder: jeder Block rendert sich selbst via /site/snippets/blocks/<type>.php
      foreach ($page->builder()->toBlocks() as $block):
        echo $block;
      endforeach;
    ?>
  </main>

  <?php snippet('footer') ?>
</body>
</html>
