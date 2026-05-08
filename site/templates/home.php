<?php

function soi_text($page, string $field, string $fallback = ''): string
{
    $value = $page->{$field}();
    return $value->isNotEmpty() ? $value->value() : $fallback;
}

function soi_html($text): string
{
    $safe = htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    $safe = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $safe);
    $safe = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $safe);
    return nl2br($safe);
}

function soi_paragraphs($text, string $leadClass = ''): string
{
    $parts = preg_split('/\R{2,}/', trim((string)$text)) ?: [];
    $html = '';

    foreach ($parts as $index => $part) {
        $class = $index === 0 && $leadClass !== '' ? ' class="' . $leadClass . '"' : '';
        $html .= '<p' . $class . '>' . soi_html(trim($part)) . '</p>';
    }

    return $html;
}

function soi_url($page, string $field, string $fallback): string
{
    $value = $page->{$field}();

    if ($value->isNotEmpty() && ($file = $value->toFiles()->first())) {
        return $file->url();
    }

    if ($value->isNotEmpty() && ($file = $value->toFile())) {
        return $file->url();
    }

    if ($value->isNotEmpty()) {
        return url(ltrim($value->value(), '/'));
    }

    return url(ltrim($fallback, '/'));
}

function soi_optional_url($page, string $field): ?string
{
    $value = $page->{$field}();

    if ($value->isNotEmpty() && ($file = $value->toFiles()->first())) {
        return $file->url() . '?v=' . $file->modified();
    }

    if ($value->isNotEmpty() && ($file = $value->toFile())) {
        return $file->url() . '?v=' . $file->modified();
    }

    if ($value->isNotEmpty()) {
        return url(ltrim($value->value(), '/'));
    }

    return null;
}

function soi_icon_library($page): array
{
    $library = [];

    foreach ($page->iconLibrary()->toStructure() as $item) {
        $key = trim($item->key()->value());
        if ($key === '') {
            continue;
        }

        $file = $item->iconFile()->toFiles()->first();
        if (!$file) {
            $file = $item->iconFile()->toFile();
        }

        if ($file) {
            $library[$key] = $file->url();
        }
    }

    return $library;
}

function soi_icon_class(string $icon): string
{
    $icon = trim($icon);
    if ($icon === '') {
        return '';
    }

    $icon = preg_replace('/[^a-zA-Z0-9_-]/', '', $icon);
    return str_starts_with($icon, 'icon-') ? $icon : 'icon-' . $icon;
}

function soi_icon(string $icon, string $extra = ''): string
{
    $class = soi_icon_class($icon);
    if ($class === '') {
        return '';
    }

    $extra = $extra !== '' ? ' ' . $extra : '';
    $icon = str_replace('icon-', '', $class);

    if ($icon === 'arrow') {
        return '<span class="site-icon' . $extra . '" aria-hidden="true"><span class="site-icon__arrow">↘</span></span>';
    }

    if ($icon === 'shooting-star' && str_contains($extra, 'star-inline')) {
        return '<span class="site-icon' . $extra . '" data-star aria-hidden="true"></span>';
    }

    $mode = trim((string)($GLOBALS['soiIconMode'] ?? 'upload'));
    if ($mode === 'svg') {
        return '<span class="site-icon' . $extra . '" aria-hidden="true"><img class="site-icon__svg" src="' . url('icons/' . $icon . '.svg') . '" alt=""></span>';
    }

    $src = ($GLOBALS['soiIconLibrary'] ?? [])[$icon] ?? url('icons/' . $icon . '.svg');
    return '<span class="site-icon' . $extra . '" aria-hidden="true"><img class="site-icon__img" src="' . esc($src) . '" alt=""></span>';
}

function soi_icon_text(string $text): string
{
    $escaped = soi_html($text);
    return preg_replace_callback('/\{icon:([a-zA-Z0-9_-]+)\}/', function ($match) {
        return soi_icon($match[1], 'manifest__icon');
    }, $escaped);
}

function soi_structure($page, string $field, array $fallback): array
{
    $items = $page->{$field}()->toStructure();
    if ($items->isEmpty()) {
        return $fallback;
    }

    return array_map(fn ($item) => $item->toArray(), $items->values());
}

function soi_item(array $item, string $key, $fallback = '')
{
    return $item[$key] ?? $item[strtolower($key)] ?? $fallback;
}

function soi_spacing($page, array $map): string
{
    $vars = [];
    foreach ($map as $field => $var) {
        $name = ltrim($var, '-');
        $desktop = $page->{$field . 'Desktop'}()->isNotEmpty() ? $page->{$field . 'Desktop'}() : $page->{$field}();
        $values = [
            'desktop' => $desktop,
            'tablet' => $page->{$field . 'Tablet'}(),
            'mobile' => $page->{$field . 'Mobile'}(),
        ];

        foreach ($values as $breakpoint => $value) {
            if ($value->isNotEmpty()) {
                $vars[] = '--' . $name . '-' . $breakpoint . ':' . (int)$value->value() . 'px';
            }
        }
    }

    return $vars === [] ? '' : ' style="' . implode(';', $vars) . '"';
}

$navItems = soi_structure($page, 'navItems', [
    ['label' => 'About', 'url' => url('about')],
    ['label' => 'Programm', 'url' => url('programm')],
    ['label' => 'Agenturen', 'url' => url('agenturen')],
    ['label' => 'Kontakt', 'url' => url() . '#contact'],
    ['label' => 'FAQ', 'url' => url('faq')],
]);

$soiIconMode = trim(soi_text($page, 'iconMode', 'upload'));
$soiIconLibrary = soi_icon_library($page);
$GLOBALS['soiIconMode'] = $soiIconMode;
$GLOBALS['soiIconLibrary'] = $soiIconLibrary;

$manifest = soi_structure($page, 'manifest', [
    ['text' => 'Kreativität verändert sich. Durch KI schneller {icon:airplane} als je zuvor. Doch nicht die Werkzeuge entscheiden über kreative {icon:shooting-star} Qualität, sondern wie du sie zu nutzen weißt. {icon:bottle}'],
    ['text' => 'Wir lehren nicht nur {icon:glasses-bold} Kreativität, sondern wir lehren den bewussten Umgang {icon:lightbulb} mit Technologie. {icon:spark} KI {icon:star} ist bei uns kein Zusatzmodul, sondern ein selbstverständliches Tool beim Denken und Arbeiten. {icon:sparkles}'],
]);

$yearBlocks = soi_structure($page, 'yearBlocks', [
    ['lead' => "Ein Jahr. Zwei\nAgenturstationen.", 'body' => "Vier Tage pro Woche arbeitest du in einer Agentur. Der Freitag gehört deiner kreativen Weiterentwicklung in der school of ideas.\n\nDu arbeitest an echten Projekten.\nUnd wirst dafür bezahlt."],
    ['lead' => "Ø 1.875 € brutto im Monat.\n0 € Studiengebühren.", 'body' => "Wir sorgen dafür, dass du und deine Agenturen zusammenpassen. Fachlich und menschlich.\n\nDu bekommst gezieltes Knowhow und persönliches Coaching. Und den Raum, dich gezielt weiterzuentwickeln."],
]);

$valuesColumns = soi_structure($page, 'valuesColumns', [
    ['lead' => 'Die Kreativbranche wird gerade auf den Kopf gestellt.', 'body' => ''],
    ['lead' => '', 'body' => 'KI mit seinen grenzenlosen Tools erzeugt ganz neue Möglichkeiten und Prozesse. Das verändert die Denk- und Arbeitsweise, wie Ideen entwickelt und umgesetzt werden.'],
    ['lead' => '', 'body' => "Genau hier setzt die school of ideas an. Du arbeitest an Ideen und Konzepten, die mit eben diesen neuen Möglichkeiten entstehen.\n\nDu lernst, wie dich die Tools in der Ideenfindung unterstützen. Wie sich kreative Prozesse verändern.\n\nUnd wie du KI gezielt einsetzt, um schneller zu außergewöhnlichen Ergebnissen zu kommen."],
]);

$audienceCards = soi_structure($page, 'audienceCards', [
    ['text' => 'Wenn du ein kreatives Fundament mitbringst und darauf aufbauen möchtest.', 'col' => 2, 'row' => 1],
    ['text' => 'Du neugierig bist auf Medien, Kultur und Zeitgeist.', 'col' => 3, 'row' => 1],
    ['text' => 'Wenn du lernen willst, wie Ideen entstehen, die Wirkung erzeugen.', 'col' => 4, 'row' => 1],
    ['text' => 'Du reale Probleme mit Kreativität lösen willst.', 'col' => 2, 'row' => 2],
    ['text' => 'Wenn du lernen möchtest, wie Kreativarbeit heute funktioniert. Und morgen relevant bleibt.', 'col' => 3, 'row' => 2],
    ['text' => 'Wenn du offen bist für neue Tools und KI als Werkzeug.', 'col' => 4, 'row' => 2],
]);

$outcomeCards = soi_structure($page, 'outcomeCards', [
    ['text' => 'Ein Portfolio, das zeigt, was du kannst. Fundierte Erfahrung in echten Teams und auf echten Kunden.', 'col' => 1, 'row' => 1],
    ['text' => 'Ein Verständnis dafür, was Marken ausmacht. Wie sie entstehen, wachsen und erfolgreich werden. Und wie Ideen dazu beitragen.', 'col' => 2, 'row' => 2],
    ['text' => 'Wichtige Kontakte in der Kreativbranche. Ein Netzwerk aus kreativen Mitstreitern, das bleibt.', 'col' => 3, 'row' => 1],
    ['text' => 'Ein System, mit dem du dich selbst reflektierst. Und gezielt weiterentwickelst.', 'col' => 3, 'row' => 2],
    ['text' => 'Kreative Fähigkeiten, die du direkt einsetzen kannst. Knowhow im Umgang mit KI und neuen Technologien.', 'col' => 4, 'row' => 1],
    ['text' => 'Ein klares Gefühl dafür, wohin du willst.', 'col' => 4, 'row' => 2],
]);

$finalCards = soi_structure($page, 'finalCards', [
    ['title' => 'Ich bin mir sicher', 'buttonText' => 'Jetzt bewerben', 'buttonUrl' => '#', 'buttonStyle' => 'mint', 'text' => ''],
    ['title' => "Ich will erst herausfinden,\nob das passt", 'buttonText' => 'Selbsttest starten', 'buttonUrl' => '#', 'buttonStyle' => 'ghost', 'text' => ''],
    ['title' => 'Ist nichts für mich', 'buttonText' => '', 'buttonUrl' => '', 'buttonStyle' => 'none', 'text' => "Dann such dir etwas,\ndas dich wirklich\nweiterbringt."],
]);

$decorativeIcons = soi_structure($page, 'decorativeIcons', [
    ['slot' => 'float-icon--lightning', 'icon' => 'lightning'],
    ['slot' => 'float-icon--airplane', 'icon' => 'airplane'],
    ['slot' => 'float-icon--ex-airplane', 'icon' => 'airplane'],
    ['slot' => 'float-icon--ex-star', 'icon' => 'shooting-star'],
    ['slot' => 'float-icon--ex-bottle', 'icon' => 'lightbulb'],
    ['slot' => 'float-icon--yr-spark', 'icon' => 'spark'],
    ['slot' => 'float-icon--yr-sparkles', 'icon' => 'sparkles'],
    ['slot' => 'float-icon--yr-document', 'icon' => 'bottle'],
    ['slot' => 'float-icon--au-shooting', 'icon' => 'shooting-star'],
    ['slot' => 'float-icon--au-comet', 'icon' => 'star'],
    ['slot' => 'float-icon--au-airplane', 'icon' => 'airplane'],
    ['slot' => 'float-icon--au-spark', 'icon' => 'spark'],
    ['slot' => 'float-icon--ou-sparkles', 'icon' => 'sparkles'],
    ['slot' => 'float-icon--ct-document', 'icon' => 'bottle'],
    ['slot' => 'float-icon--ct-shooting', 'icon' => 'shooting-star'],
    ['slot' => 'float-icon--ct-bottle', 'icon' => 'lightbulb'],
    ['slot' => 'float-icon--ct-glasses', 'icon' => 'glasses-bold'],
]);

$iconsBySlot = [];
foreach ($decorativeIcons as $item) {
    $iconsBySlot[soi_item($item, 'slot')] = soi_item($item, 'icon');
}

$renderFloatIcon = function (string $slot) use ($iconsBySlot): void {
    echo '<span class="float-icon ' . htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true">';
    echo soi_icon($iconsBySlot[$slot] ?? '');
    echo '</span>';
};

$renderCard = function (array $card): void {
    $col = max(1, (int)soi_item($card, 'col', 1));
    $row = max(1, (int)soi_item($card, 'row', 1));
    echo '<article class="card" style="--card-col:' . $col . ';--card-row:' . $row . '">';
    echo soi_icon('arrow', 'card__arrow');
    echo '<p class="card__text">' . soi_html(soi_item($card, 'text')) . '</p>';
    echo '</article>';
};

?><!doctype html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($page->title()) ?> - <?= esc($site->title()) ?></title>
  <meta name="description" content="<?= esc($site->siteDescription()->or('Dein Einstieg in die Kreativbranche. Und der Anfang einer richtig guten Karriere.')) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= url('logo/School_of_ideas_Bildlogo.svg') ?>">
  <?php snippet('vite') ?>
</head>
<body>
  <header class="nav" id="siteNav" data-glass="false">
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
            <?php foreach ($navItems as $item): ?>
            <li><a class="nav__link" href="<?= esc($item['url'] ?? '#') ?>"><?= esc($item['label'] ?? '') ?></a></li>
            <?php endforeach ?>
          </ul>
        </nav>
        <a href="<?= esc(soi_text($page, 'ctaUrl', '#contact')) ?>" class="nav__cta"><?= esc(soi_text($page, 'ctaLabel', 'Bewerbung')) ?></a>
        <button class="nav__burger" id="navBurger" aria-label="Menü öffnen" aria-expanded="false" aria-controls="mobileMenu" type="button"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>

  <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
    <nav class="mobile-menu__nav" aria-label="Mobile navigation">
      <ul class="mobile-menu__list">
        <?php foreach ($navItems as $item): ?>
        <li><a href="<?= esc($item['url'] ?? '#') ?>" class="mobile-menu__link"><span><?= esc($item['label'] ?? '') ?></span></a></li>
        <?php endforeach ?>
        <li><a href="<?= esc(soi_text($page, 'ctaUrl', '#contact')) ?>" class="mobile-menu__link mobile-menu__link--cta"><span><?= esc(soi_text($page, 'ctaLabel', 'Bewerbung')) ?></span></a></li>
      </ul>
    </nav>
  </div>

  <?php
  $heroVideoMobile = soi_optional_url($page, 'heroVideoMobile');
  $heroVideo = soi_optional_url($page, 'heroVideo');
  ?>
  <section class="hero" id="hero">
    <video
      class="hero__video"
      autoplay
      muted
      loop
      playsinline
      webkit-playsinline
      preload="metadata"
      poster="<?= soi_url($page, 'heroPoster', 'images/school_of_ideas_motiv_Header.jpg') ?>"
      <?php if ($heroVideoMobile): ?>data-mobile-src="<?= esc($heroVideoMobile) ?>"<?php endif ?>
      <?php if ($heroVideo): ?>data-desktop-src="<?= esc($heroVideo) ?>"<?php endif ?>
    >
      <?php if ($heroVideoMobile): ?>
      <source src="<?= esc($heroVideoMobile) ?>" type="video/mp4" media="(max-width: 768px)">
      <?php endif ?>
      <?php if ($heroVideo): ?>
      <source src="<?= esc($heroVideo) ?>" type="video/mp4">
      <?php endif ?>
    </video>
    <div class="hero__overlay"></div>
    <div class="hero__content">
      <h1 class="hero__title">
        <span class="line"><?= soi_html(soi_text($page, 'heroLine1', 'Kreativität hat')) ?></span>
        <span class="line"><?= soi_html(soi_text($page, 'heroLine2Prefix', 'immer')) ?> <?= soi_icon(soi_text($page, 'heroIcon', 'shooting-star'), 'star-inline') ?> <?= soi_html(soi_text($page, 'heroLine2Suffix', 'Zukunft')) ?></span>
      </h1>
    </div>
  </section>
  <div class="hero__mint" aria-hidden="true"></div>

  <section class="intro" id="about"<?= soi_spacing($page, [
    'introTop' => '--intro-spacing-top',
    'introBottom' => '--intro-spacing-bottom',
    'introMotivGap' => '--motiv-spacing-top',
    'introTextGap' => '--intro-copy-gap',
    'introCtaGap' => '--intro-cta-spacing-top',
  ]) ?>>
    <div class="container">
      <div class="intro__grid">
        <div class="intro__col intro__col--left">
          <h2 class="intro__lead"><span class="ink"><?= soi_html(soi_text($page, 'introHeadlineInk', "Dein Einstieg in die\nKreativbranche.")) ?></span><span class="accent"><?= soi_html(soi_text($page, 'introHeadlineAccent', "Und der Anfang einer\nrichtig guten Karriere.")) ?></span></h2>
          <?php $renderFloatIcon('float-icon--lightning') ?>
          <figure class="motiv motiv--shadow" data-motiv-shadow data-rotate="-8" data-shadow-x="44" data-shadow-y="40" data-shadow-rotate="6">
            <span class="motiv__shadow" aria-hidden="true"></span>
            <div class="motiv__frame"><img class="motiv__img" src="<?= soi_url($page, 'introImage', 'images/school_of_ideas_motiv_01.jpg') ?>" alt=""></div>
          </figure>
        </div>
        <div class="intro__col intro__col--right">
          <p class="intro__sub"><?= soi_html(soi_text($page, 'introSub', "Die school of ideas verbindet echte\nkreative Arbeit in Agenturen mit\ngezielter Weiterentwicklung.")) ?></p>
          <div class="intro__body"><?= soi_paragraphs(soi_text($page, 'introBody', "Du beschäftigst dich mit realen Projekten und lernst Ideen zu entwickeln, zu schärfen und umzusetzen.\n\nDu verdienst von Anfang an Geld und entwickelst dein kreatives Profil weiter."), 'text-medium') ?></div>
          <div class="intro__ctas"><a class="btn-mint" href="#contact">Jetzt bewerben</a><a class="btn-ghost" href="#contact">Selbsttest starten</a></div>
          <?php $renderFloatIcon('float-icon--airplane') ?>
        </div>
      </div>
    </div>
  </section>

  <section class="manifest"<?= soi_spacing($page, [
    'manifestTop' => '--manifest-spacing-top',
    'manifestBottom' => '--manifest-spacing-bottom',
    'manifestParagraphGap' => '--manifest-paragraph-gap',
    'manifestIconSize' => '--manifest-icon-size',
    'manifestIconGap' => '--manifest-icon-gap',
    'manifestIconLift' => '--manifest-icon-lift',
    'manifestIconStroke' => '--manifest-icon-stroke',
  ]) ?>>
    <div class="container"><div class="manifest__inner">
      <?php foreach ($manifest as $item): ?><p class="manifest__text"><?= soi_icon_text($item['text'] ?? '') ?></p><?php endforeach ?>
    </div></div>
  </section>

  <section class="expect" id="program"<?= soi_spacing($page, [
    'expectTop' => '--expect-spacing-top',
    'expectBottom' => '--expect-spacing-bottom',
    'expectCopyGap' => '--expect-copy-gap',
    'expectMotivGap' => '--expect-motiv-spacing-top',
  ]) ?>>
    <div class="container">
      <header class="expect__head">
        <h2 class="expect__lead"><span class="ink"><?= soi_html(soi_text($page, 'expectHeadlineInk', 'Was dich bei uns')) ?></span><span class="accent"><?= soi_html(soi_text($page, 'expectHeadlineAccent', 'erwartet:')) ?></span></h2>
        <div class="expect__copy"><p class="expect__sub"><?= soi_html(soi_text($page, 'expectSub', "Wir sind kein Studium. Und\nkeine klassische Ausbildung.")) ?></p><div class="expect__body"><?= soi_paragraphs(soi_text($page, 'expectBody', "Die school of ideas ist ein einjähriges Programm für Talente, die in kreativen Berufen arbeiten wollen.\n\nWir verbinden bezahlte Praxis in Agenturen mit gezielter Weiterentwicklung und persönlichem Coaching. Konsequent ausgerichtet auf die Kreativarbeit von heute und deine Karriere von morgen."), 'text-medium') ?></div></div>
      </header>
      <figure class="motiv motiv--shadow expect__motiv" data-motiv-shadow data-rotate="20" data-shadow-x="56" data-shadow-y="48" data-shadow-rotate="-9"><span class="motiv__shadow" aria-hidden="true"></span><div class="motiv__frame"><img class="motiv__img" src="<?= soi_url($page, 'expectImage', 'images/school_of_ideas_motiv_03.jpg') ?>" alt=""></div></figure>
      <?php $renderFloatIcon('float-icon--ex-airplane'); $renderFloatIcon('float-icon--ex-star'); $renderFloatIcon('float-icon--ex-bottle') ?>
    </div>
  </section>

  <section class="year" id="year"<?= soi_spacing($page, [
    'yearTop' => '--year-spacing-top',
    'yearBottom' => '--year-spacing-bottom',
    'yearCopyGap' => '--year-copy-spacing-top',
    'yearRowGap' => '--year-block-row-gap',
    'yearColGap' => '--year-block-col-gap',
    'yearMotivGap' => '--year-motiv-spacing-top',
  ]) ?>>
    <div class="container">
      <h2 class="year__lead"><span class="ink"><?= soi_html(soi_text($page, 'yearHeadlineInk', 'So sieht dein Jahr')) ?></span><span class="accent"><?= soi_html(soi_text($page, 'yearHeadlineAccent', 'bei uns aus:')) ?></span></h2>
      <div class="year__copy">
        <?php foreach ($yearBlocks as $index => $block): $n = $index + 1; ?>
        <div class="year__block year__block--lead-<?= $n ?>"><p class="year__block-lead"><?= soi_html($block['lead'] ?? '') ?></p></div>
        <div class="year__block year__block--body-<?= $n ?>"><?= soi_paragraphs($block['body'] ?? '', 'text-medium') ?></div>
        <?php endforeach ?>
      </div>
      <figure class="motiv motiv--shadow year__motiv" data-motiv-shadow data-rotate="-5" data-shadow-x="-50" data-shadow-y="40" data-shadow-rotate="7"><span class="motiv__shadow" aria-hidden="true"></span><div class="motiv__frame"><img class="motiv__img" src="<?= soi_url($page, 'yearImage', 'images/school_of_ideas_motiv_02.jpg') ?>" alt=""></div></figure>
      <?php $renderFloatIcon('float-icon--yr-spark'); $renderFloatIcon('float-icon--yr-sparkles'); $renderFloatIcon('float-icon--yr-document') ?>
    </div>
  </section>

  <section class="motiv-stack" id="motiv-stack"><svg class="cloud-trail" id="cloudTrail" viewBox="0 0 225.35 217.44" aria-hidden="true"><path fill="#fff" stroke="#141618" stroke-width="7" stroke-miterlimit="10" d="M177.39,121.2c5.74-14.12-10.35-24.14-24.65-18.18-.53.22-4.97,2.77-5.84,3.26-1.04-9.07-5.37-21.64-21.36-22.53-3.38-.19-6.59.75-9.47,2.1-4.09-9.18-15.39-18.14-32.89-15.58-14.35,2.1-24.28,14.38-17.36,26.26.32.54.8,1.2,1.28,1.79-.62.26-2.44,1.03-2.55,1.07-9.6,3.61-17.77,13.53-17.77,21.77,0,10.5,9.62,12.84,16.59,14.91,19.29,5.73,55.9,4.01,77.5.65,9.23-1.44,32.81-6.36,36.53-15.51Z"/><path fill="#fff" stroke="#141618" stroke-width="7" stroke-miterlimit="10" d="M112.18,110.62c-5.25-4.46-20.69-9.99-31.94,3.94"/></svg></section>

  <section class="values" id="values"<?= soi_spacing($page, [
    'valuesTop' => '--values-spacing-top',
    'valuesBottom' => '--values-spacing-bottom',
    'valuesGridGap' => '--values-grid-spacing-top',
    'valuesColGap' => '--values-grid-col-gap',
    'valuesParagraphGap' => '--values-col-paragraph-gap',
  ]) ?>>
    <div class="container">
      <h2 class="values__lead"><span class="ink"><?= soi_html(soi_text($page, 'valuesHeadlineInk', 'Was heute wirklich')) ?></span><span class="accent"><?= soi_html(soi_text($page, 'valuesHeadlineAccent', 'zählt')) ?></span></h2>
      <div class="values__grid">
        <?php foreach ($valuesColumns as $col): ?><div class="values__col"><?php if (($col['lead'] ?? '') !== ''): ?><p class="values__col-lead"><?= soi_html($col['lead']) ?></p><?php endif ?><?= soi_paragraphs($col['body'] ?? '', 'text-medium') ?></div><?php endforeach ?>
      </div>
    </div>
  </section>

  <section class="audience" id="audience"<?= soi_spacing($page, [
    'audienceTop' => '--audience-spacing-top',
    'audienceBottom' => '--audience-spacing-bottom',
    'audienceSubGap' => '--audience-sub-spacing-top',
    'audienceGridGap' => '--audience-grid-spacing-top',
    'cardColGap' => '--grid-col-gap',
    'cardRowGap' => '--grid-row-gap',
  ]) ?>>
    <div class="container">
      <header class="audience__head"><h2 class="audience__lead"><span class="ink"><?= soi_html(soi_text($page, 'audienceHeadlineInk', 'Für wen das Sinn')) ?></span><span class="accent"><?= soi_html(soi_text($page, 'audienceHeadlineAccent', 'macht')) ?></span></h2><p class="audience__sub"><?= soi_html(soi_text($page, 'audienceSub', "Für dich, wenn du mit deiner\nKreativität was bewegen und\nGeld verdienen willst.")) ?></p></header>
      <div class="card-grid"><?php foreach ($audienceCards as $card) $renderCard($card); ?></div>
      <?php $renderFloatIcon('float-icon--au-shooting'); $renderFloatIcon('float-icon--au-comet'); $renderFloatIcon('float-icon--au-airplane'); $renderFloatIcon('float-icon--au-spark') ?>
    </div>
  </section>

  <section class="outcome" id="outcome"<?= soi_spacing($page, [
    'outcomeTop' => '--outcome-spacing-top',
    'outcomeBottom' => '--outcome-spacing-bottom',
    'outcomeGridGap' => '--outcome-grid-spacing-top',
    'cardColGap' => '--grid-col-gap',
    'cardRowGap' => '--grid-row-gap',
  ]) ?>>
    <div class="container">
      <header class="outcome__head"><h2 class="outcome__lead"><span class="ink"><?= soi_html(soi_text($page, 'outcomeHeadlineInk', 'Was du am Ende')) ?></span><span class="accent"><?= soi_html(soi_text($page, 'outcomeHeadlineAccent', 'mitnimmst')) ?></span></h2></header>
      <div class="card-grid"><?php foreach ($outcomeCards as $card) $renderCard($card); ?></div>
      <?php $renderFloatIcon('float-icon--ou-sparkles') ?>
    </div>
  </section>

  <section class="cta-final" id="contact"<?= soi_spacing($page, [
    'finalTop' => '--cta-final-spacing-top',
    'finalBottom' => '--cta-final-spacing-bottom',
    'finalTitleGap' => '--cta-final-title-spacing-top',
    'finalCardsGap' => '--cta-final-cards-spacing-top',
    'finalCardColGap' => '--cta-final-cards-gap',
    'finalCardRowGap' => '--cta-final-card-row-gap',
  ]) ?>>
    <div class="container">
      <figure class="motiv motiv--shadow cta-final__motiv" data-motiv-shadow data-rotate="-5" data-shadow-x="-60" data-shadow-y="50" data-shadow-rotate="6"><span class="motiv__shadow" aria-hidden="true"></span><div class="motiv__frame"><img class="motiv__img" src="<?= soi_url($page, 'finalImage', 'images/school_of_ideas_motiv_04.jpg') ?>" alt=""></div></figure>
      <?php $renderFloatIcon('float-icon--ct-document'); $renderFloatIcon('float-icon--ct-shooting'); $renderFloatIcon('float-icon--ct-bottle'); $renderFloatIcon('float-icon--ct-glasses') ?>
      <h2 class="cta-final__title"><span class="ink"><?= soi_html(soi_text($page, 'finalHeadlineInk', 'Kreativität hat immer Zukunft')) ?></span><span class="accent"><?= soi_html(soi_text($page, 'finalHeadlineAccent', 'Wir bilden die Talente aus, die sie gestalten.')) ?></span></h2>
      <div class="cta-final__cards">
        <?php foreach ($finalCards as $card): ?>
        <article class="cta-final__card">
          <?= soi_icon('arrow', 'card__arrow') ?>
          <h3 class="cta-final__card-title"><?= soi_html(soi_item($card, 'title')) ?></h3>
          <?php $buttonStyle = soi_item($card, 'buttonStyle', 'none'); $buttonText = soi_item($card, 'buttonText'); ?>
          <?php if ($buttonStyle !== 'none' && $buttonText !== ''): ?><a class="<?= $buttonStyle === 'ghost' ? 'btn-ghost' : 'btn-mint' ?>" href="<?= esc(soi_item($card, 'buttonUrl', '#')) ?>"><?= esc($buttonText) ?></a><?php else: ?><p class="cta-final__card-text"><?= soi_html(soi_item($card, 'text')) ?></p><?php endif ?>
        </article>
        <?php endforeach ?>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="footer__mint" aria-hidden="true"></div>
    <div class="container">
      <p class="footer__claim"><span class="ink"><?= soi_html(soi_text($page, 'footerClaimInk', 'Bereit, deine Kreativität')) ?></span><span class="accent"><?= soi_html(soi_text($page, 'footerClaimAccent', 'in Zukunft zu verwandeln?')) ?></span></p>
      <div class="footer__row">
        <a class="footer__brand" href="<?= url() ?>" aria-label="school of ideas"><span class="footer__brand-stack"><img class="footer__brand-text" src="<?= url('logo/School_of_ideas_Logo_OW.png') ?>" alt="school of ideas"><span class="footer__cloud-slot" data-cloud-target aria-hidden="true"></span></span></a>
        <nav class="footer__nav" aria-label="Footer-Navigation"><?php foreach (soi_structure($page, 'footerLinks', $navItems) as $item): ?><a href="<?= esc($item['url'] ?? '#') ?>"><?= esc($item['label'] ?? '') ?></a><?php endforeach ?></nav>
      </div>
      <div class="footer__bottom">
        <div class="footer__legal"><?php foreach (soi_structure($page, 'legalLinks', [['label' => 'Impressum', 'url' => '#'], ['label' => 'Datenschutz', 'url' => '#'], ['label' => 'Cookies', 'url' => '#']]) as $item): ?><a href="<?= esc($item['url'] ?? '#') ?>"><?= esc($item['label'] ?? '') ?></a><?php endforeach ?></div>
        <p class="footer__credit">Made with <span class="footer__heart" aria-hidden="true">♥</span><span class="visually-hidden">love</span> in Heimbach by <a class="footer__studio" href="https://watm.bajuku-dev.de/" target="_blank" rel="noopener">we and the machine</a> <span class="footer__machine" aria-hidden="true"><span></span></span></p>
      </div>
    </div>
  </footer>
</body>
</html>
