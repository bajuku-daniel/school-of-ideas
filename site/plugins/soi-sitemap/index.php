<?php
/**
 * SOI Sitemap
 * -----------
 * Endpoint /sitemap.xml — listet alle "listed" Pages mit lastmod.
 * Versteckte Pages (z.B. styleguide via unlisted-Status) werden nicht
 * aufgenommen. Templates wie agency (Detail-Seiten) sind auch enthalten,
 * solange sie listed sind.
 */

Kirby::plugin('soi/sitemap', [
    'routes' => [
        [
            'pattern' => 'sitemap.xml',
            'method'  => 'GET',
            'action'  => function () {
                $kirby = kirby();
                $pages = site()->index()->listed();

                $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
                $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

                foreach ($pages as $page) {
                    // Auf der sicheren Seite: noindex/styleguide auslassen
                    if ($page->intendedTemplate()->name() === 'styleguide') continue;

                    $lastmod = date('Y-m-d', $page->modified());
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . esc($page->url()) . "</loc>\n";
                    $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "  </url>\n";
                }

                $xml .= '</urlset>';

                return new \Kirby\Http\Response(
                    $xml,
                    'application/xml',
                    200
                );
            }
        ]
    ]
]);
