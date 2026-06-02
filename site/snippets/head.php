<?php
/**
 * Zentrales <head>-Snippet — gemeinsam für alle Templates.
 * Liefert: Title, Meta-Description, Canonical, Open Graph, Twitter Card,
 * Favicons (SVG + PNG + Apple-Touch) und Robots-Hint.
 *
 * Pro Template einfach via:
 *   <?php snippet('head', ['noindex' => true]) ?>
 * um z.B. den Style-Guide aus dem Index zu nehmen.
 */
$noindex   = $noindex ?? false;
$ogImage   = $ogImage ?? null;        // optionaler Override
$siteName  = (string)$site->title();
$sep       = ' — ';

// ----- Title -----
if ($page->isHomePage()) {
    $title = $siteName;
} else {
    $title = (string)$page->title() . $sep . $siteName;
}

// ----- Description -----
// Reihenfolge: page.metaDescription → page.intro → site.siteDescription → Default
$desc = (string)$page->metaDescription();
if ($desc === '' && $page->intro()->isNotEmpty()) {
    $desc = $page->intro()->excerpt(160)->value();
}
if ($desc === '') {
    $desc = (string)$site->siteDescription();
}
if ($desc === '') {
    $desc = 'Dein Einstieg in die Kreativbranche. Und der Anfang einer richtig guten Karriere.';
}

// ----- Canonical -----
$canonical = $page->url();

// ----- OG-Image: explizit > first image im Page-Folder > default Logo -----
if (!$ogImage) {
    $firstImg = $page->images()->filterBy('extension', 'in', ['jpg','jpeg','png','webp'])->first();
    $ogImage  = $firstImg ? $firstImg->url() : url('logo/School_of_ideas_Bildlogo.svg');
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title) ?></title>
<meta name="description" content="<?= esc($desc) ?>">
<link rel="canonical" href="<?= esc($canonical) ?>">

<?php if ($noindex): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow">
<?php endif ?>

<!-- Open Graph -->
<meta property="og:type" content="<?= $page->isHomePage() ? 'website' : 'article' ?>">
<meta property="og:site_name" content="<?= esc($siteName) ?>">
<meta property="og:title" content="<?= esc($title) ?>">
<meta property="og:description" content="<?= esc($desc) ?>">
<meta property="og:url" content="<?= esc($canonical) ?>">
<meta property="og:image" content="<?= esc($ogImage) ?>">
<meta property="og:locale" content="de_DE">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= esc($title) ?>">
<meta name="twitter:description" content="<?= esc($desc) ?>">
<meta name="twitter:image" content="<?= esc($ogImage) ?>">

<!-- Favicons -->
<link rel="icon" type="image/svg+xml" href="<?= url('logo/School_of_ideas_Bildlogo.svg') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= url('favicon-32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= url('favicon-16.png') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= url('apple-touch-icon.png') ?>">
<link rel="manifest" href="<?= url('site.webmanifest') ?>">
<meta name="theme-color" content="#1a3aff">

<?php snippet('vite') ?>
