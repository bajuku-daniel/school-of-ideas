<?php
$items = [
    ['label' => 'About', 'url' => url('about')],
    ['label' => 'Programm', 'url' => url('programm')],
    ['label' => 'Agenturen', 'url' => url('agenturen')],
    ['label' => 'Kontakt', 'url' => url('kontakt')],
    ['label' => 'FAQ', 'url' => url('faq')],
];
$activePage = $page;
if ($page->intendedTemplate()->name() === 'agency' && $page->parent()) {
    $activePage = $page->parent();
}
?>
<header class="nav nav--subpage is-glass" id="siteNav">
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
          <?php foreach ($items as $item): ?>
          <li><a class="nav__link<?= $activePage->url() === $item['url'] ? ' is-active' : '' ?>" href="<?= $item['url'] ?>"><?= esc($item['label']) ?></a></li>
          <?php endforeach ?>
        </ul>
      </nav>
      <a href="<?= url('bewerbung') ?>" class="nav__cta">Bewerbung</a>
      <button class="nav__burger" id="navBurger" aria-label="Menü öffnen" aria-expanded="false" aria-controls="mobileMenu" type="button"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <nav class="mobile-menu__nav" aria-label="Mobile navigation">
    <ul class="mobile-menu__list">
      <?php foreach ($items as $item): ?>
      <li><a href="<?= $item['url'] ?>" class="mobile-menu__link"><span><?= esc($item['label']) ?></span></a></li>
      <?php endforeach ?>
      <li><a href="<?= url('bewerbung') ?>" class="mobile-menu__link mobile-menu__link--cta"><span>Bewerbung</span></a></li>
    </ul>
  </nav>
</div>
