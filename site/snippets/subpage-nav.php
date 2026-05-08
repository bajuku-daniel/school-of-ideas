<?php
$items = [
    ['label' => 'About', 'url' => url('about')],
    ['label' => 'Programm', 'url' => url('programm')],
    ['label' => 'Agenturen', 'url' => url('agenturen')],
    ['label' => 'Kontakt', 'url' => url() . '#contact'],
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
          <img class="nav__brand-text" src="<?= url('logo/School_of_ideas_Logo_OW.png') ?>" alt="school of ideas">
          <span class="nav__cloud" aria-hidden="true">
            <svg viewBox="0 0 225.35 217.44" xmlns="http://www.w3.org/2000/svg"><path fill="#fff" stroke="currentColor" stroke-width="9" stroke-miterlimit="10" d="M177.39,121.2c5.74-14.12-10.35-24.14-24.65-18.18-.53.22-4.97,2.77-5.84,3.26-1.04-9.07-5.37-21.64-21.36-22.53-3.38-.19-6.59.75-9.47,2.1-4.09-9.18-15.39-18.14-32.89-15.58-14.35,2.1-24.28,14.38-17.36,26.26.32.54.8,1.2,1.28,1.79-.62.26-2.44,1.03-2.55,1.07-9.6,3.61-17.77,13.53-17.77,21.77,0,10.5,9.62,12.84,16.59,14.91,19.29,5.73,55.9,4.01,77.5.65,9.23-1.44,32.81-6.36,36.53-15.51Z"/><path fill="none" stroke="currentColor" stroke-width="9" stroke-miterlimit="10" d="M112.18,110.62c-5.25-4.46-20.69-9.99-31.94,3.94"/></svg>
          </span>
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
