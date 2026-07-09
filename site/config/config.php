<?php

// =============================================================================
// 301-Weiterleitungen der ALTEN Domain (school-of-ideas.hamburg, ehem.
// WordPress) auf die neue Domain schoolofideas.de.
// Voraussetzung: A-Record von school-of-ideas.hamburg zeigt auf dieselbe
// Mittwald-IP wie schoolofideas.de → beide Domains kommen auf diesem Kirby an.
// Kirby unterscheidet per Host-Header und leitet die alte Domain 301 um.
// Bekannte alte URLs → passende neue Seite (SEO-Linkjuice erhalten),
// alles Übrige → Startseite (vermeidet 404 auf alten Backlinks).
// =============================================================================
$soiLegalHost   = 'school-of-ideas.hamburg';   // alte Domain (deckt www. mit ab)
$soiTargetOrigin = 'https://schoolofideas.de';  // neue Haupt-Domain

// Alte WordPress-URL (ohne führenden/abschließenden Slash) → neue Seite.
$soiRedirectMap = [
    'die-neue-texterschmiede'                                                                 => '/',
    'ausbilden/bewerben/aufnahmebedingungen-und-kosten-der-ausbildung-hamburg-school-of-ideas' => '/programm',
    'ausbilden/bewerben'                                                                       => '/bewerbung',
    'ausbilden/schulkonzept'                                                                   => '/about',
    'home-new-2-2'                                                                             => '/about',
];

// Gemeinsame Redirect-Aktion — wird für Home ('') und alle Pfade genutzt.
$soiRedirect = function ($path = '') use ($soiLegalHost, $soiTargetOrigin, $soiRedirectMap) {
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');

    if (str_contains($host, $soiLegalHost)) {
        $clean = trim((string)$path, '/');
        $to    = $soiRedirectMap[$clean] ?? '/';   // Fallback: Startseite
        go($soiTargetOrigin . $to, 301);
    }

    // Normale (neue) Domain: unverändert weiter im Kirby-Router.
    $this->next();
};

return [
    'debug' => false,
    'panel' => [
        'install' => false
    ],
    'routes' => [
        ['pattern' => '',       'action' => $soiRedirect],  // Startseite (leerer Pfad)
        ['pattern' => '(:all)', 'action' => $soiRedirect],  // alle übrigen Pfade
    ],
];
