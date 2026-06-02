<?php
/**
 * Shared Footer Snippet — Design Björn.
 * Liest alle Felder aus dem Footer-Tab im Site-Blueprint.
 */

$claim          = (string)$site->footerClaim()->or('Kreativität hat immer Zukunft');
$claimSub       = (string)$site->footerClaimSub()->or('Und wir bilden die Talente aus, die sie gestalten.');

$ctaApplyHead   = (string)$site->ctaApplyHead()->or('Du willst Kreativität zum Beruf machen?');
$ctaApplyBtn    = (string)$site->ctaApplyButton()->or('Jetzt bewerben');
$ctaApplyUrl    = (string)$site->ctaApplyUrl()->or('/kreativtest');

$ctaAgencyHead  = (string)$site->ctaAgencyHead()->or('Du hast eine Agentur und suchst Nachwuchs?');
$ctaAgencyBtn   = (string)$site->ctaAgencyButton()->or('Jetzt mitmachen');
$ctaAgencyUrl   = (string)$site->ctaAgencyUrl()->or('mailto:mail@schoolofideas.de');

$copyright      = (string)$site->copyright()->or('© ' . date('Y') . ' School of Ideas gGmbH');
$creditPrefix   = (string)$site->creditPrefix()->or('Website by');
$creditStudio   = (string)$site->creditStudio()->or('we and the machine');
$creditUrl      = (string)$site->creditUrl()->or('https://watm.bajuku-dev.de/');

// Spalten-Definition mit Fallback auf Default-Items aus Björns Brief.
$defaults = [
    'contact' => [
        'title' => 'Kontakt',
        'items' => [
            ['label' => 'Romy Nickel',                'url' => '', 'hidden' => false],
            ['label' => 'Leitung school of ideas',    'url' => '', 'hidden' => false],
            ['label' => 'mail@schoolofideas.de',      'url' => 'mailto:mail@schoolofideas.de', 'hidden' => false],
        ],
    ],
    'program' => [
        'title' => 'Das Programm',
        'items' => [
            ['label' => 'Junior Creative Studies',    'url' => '/programm#worum-es-hier-wirklich-geht', 'hidden' => false],
            ['label' => 'Curriculum',                  'url' => '', 'hidden' => true],
            ['label' => 'FAQ',                         'url' => '/faq#das-programm', 'hidden' => false],
        ],
    ],
    'application' => [
        'title' => 'Deine Bewerbung',
        'items' => [
            ['label' => 'Jetzt bewerben',              'url' => '/kreativtest', 'hidden' => false],
            ['label' => 'Voraussetzungen',             'url' => '/#fuer-wen-das-sinn-macht', 'hidden' => false],
            ['label' => 'Termine',                     'url' => '', 'hidden' => true],
        ],
    ],
    'agencies' => [
        'title' => 'Für Agenturen',
        'items' => [
            ['label' => 'Partneragentur werden',       'url' => 'mailto:mail@schoolofideas.de', 'hidden' => false],
            ['label' => 'Mehr Informationen (Preise)', 'url' => '', 'hidden' => true],
            ['label' => 'FAQ für Agenturen',           'url' => '/faq#fuer-agenturen', 'hidden' => false],
        ],
    ],
];

$columnFields = [
    'contact'     => 'contactColumn',
    'program'     => 'programColumn',
    'application' => 'applicationColumn',
    'agencies'    => 'agenciesColumn',
];

$columns = [];
foreach ($columnFields as $key => $field) {
    try {
        $struct = $site->{$field}()->toStructure();
    } catch (\Throwable $e) {
        $struct = null;
    }

    if ($struct && !$struct->isEmpty()) {
        // Aus dem Panel: erstes Item kann (muss aber nicht) der Spaltentitel sein.
        $items = [];
        $titleOverride = $defaults[$key]['title'];
        $first = true;
        foreach ($struct as $item) {
            $label  = (string)$item->label();
            $url    = (string)$item->url();
            $hidden = (bool)$item->hidden()->toBool();
            // Wenn das erste Item den Default-Spaltentitel matched → als Titel nutzen
            if ($first && strcasecmp(trim($label), trim($titleOverride)) === 0) {
                $titleOverride = $label;
                $first = false;
                continue;
            }
            $first = false;
            $items[] = ['label' => $label, 'url' => $url, 'hidden' => $hidden];
        }
        $columns[] = ['title' => $titleOverride, 'items' => $items];
    } else {
        $columns[] = $defaults[$key];
    }
}

// Legal-Links: aus Site oder Defaults
$legalLinks = [];
try {
    $struct = $site->legalLinks()->toStructure();
    if (!$struct->isEmpty()) {
        foreach ($struct as $l) {
            $legalLinks[] = ['label' => (string)$l->label(), 'url' => (string)$l->url()];
        }
    }
} catch (\Throwable $e) {}
if (empty($legalLinks)) {
    $legalLinks = [
        ['label' => 'Impressum',   'url' => '/impressum'],
        ['label' => 'Datenschutz', 'url' => '/datenschutz'],
    ];
}
?>
<footer class="footer-v2">
  <!-- Logo-Bereich oben (gleiche BG wie Page, Inhalt boxed wie Content) -->
  <div class="footer-v2__logo-wrap">
    <div class="footer-v2__inner">
      <a class="footer-v2__brand" href="<?= url() ?>" aria-label="school of ideas">
        <span class="footer-v2__brand-stack">
          <img class="footer-v2__brand-text" src="<?= url('logo/soi_logo_wordmark.svg') ?>" alt="school of ideas">
          <span class="footer-v2__cloud-slot" data-cloud-target aria-hidden="true"></span>
        </span>
      </a>
    </div>
  </div>

  <!-- Blauer Hauptbereich -->
  <div class="footer-v2__main">
    <div class="footer-v2__inner">

      <!-- Top-Row: Claim (2 Cols) + 2 CTAs -->
      <div class="footer-v2__top">
        <div class="footer-v2__claim-wrap">
          <?php if ($claim !== ''): ?>
            <p class="footer-v2__claim"><?= soi_html($claim) ?></p>
          <?php endif ?>
          <?php if ($claimSub !== ''): ?>
            <p class="footer-v2__claim-sub"><?= soi_html($claimSub) ?></p>
          <?php endif ?>
        </div>

        <div class="footer-v2__cta">
          <?php if ($ctaApplyHead !== ''): ?>
            <p class="footer-v2__cta-head"><?= soi_html($ctaApplyHead) ?></p>
          <?php endif ?>
          <?php if ($ctaApplyBtn !== ''): ?>
            <a class="footer-v2__btn" href="<?= esc($ctaApplyUrl !== '' ? $ctaApplyUrl : '#') ?>"><?= esc($ctaApplyBtn) ?></a>
          <?php endif ?>
        </div>

        <div class="footer-v2__cta">
          <?php if ($ctaAgencyHead !== ''): ?>
            <p class="footer-v2__cta-head"><?= soi_html($ctaAgencyHead) ?></p>
          <?php endif ?>
          <?php if ($ctaAgencyBtn !== ''): ?>
            <a class="footer-v2__btn" href="<?= esc($ctaAgencyUrl !== '' ? $ctaAgencyUrl : '#') ?>"><?= esc($ctaAgencyBtn) ?></a>
          <?php endif ?>
        </div>
      </div>

      <!-- 4-Spalten-Grid -->
      <div class="footer-v2__cols">
        <?php foreach ($columns as $col):
          $visible = array_filter($col['items'], fn($i) => !$i['hidden'] && $i['label'] !== '');
          if (empty($visible)) continue;
        ?>
          <div class="footer-v2__col">
            <h3 class="footer-v2__col-title"><?= esc($col['title']) ?></h3>
            <ul class="footer-v2__list">
              <?php foreach ($visible as $item):
                $isExternal = str_starts_with($item['url'], 'http');
              ?>
                <li>
                  <?php if ($item['url'] !== ''): ?>
                    <a href="<?= esc($item['url']) ?>"<?= $isExternal ? ' target="_blank" rel="noopener"' : '' ?>><?= esc($item['label']) ?></a>
                  <?php else: ?>
                    <span><?= esc($item['label']) ?></span>
                  <?php endif ?>
                </li>
              <?php endforeach ?>
            </ul>
          </div>
        <?php endforeach ?>
      </div>

      <!-- Bottom-Row: Copyright + Legal + Credit -->
      <div class="footer-v2__bottom">
        <?php if ($copyright !== ''): ?>
          <p class="footer-v2__copyright"><?= soi_html($copyright) ?></p>
        <?php endif ?>

        <?php if (!empty($legalLinks)): ?>
          <ul class="footer-v2__legal">
            <?php foreach ($legalLinks as $l):
              if ($l['label'] === '') continue;
            ?>
              <li><a href="<?= esc($l['url'] !== '' ? $l['url'] : '#') ?>"><?= esc($l['label']) ?></a></li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>

        <?php if ($creditStudio !== ''): ?>
          <!-- <p class="footer-v2__credit">
            <?= esc($creditPrefix !== '' ? $creditPrefix : 'Website by') ?>
            <a href="<?= esc($creditUrl !== '' ? $creditUrl : '#') ?>" target="_blank" rel="noopener"><?= esc($creditStudio) ?></a>
          </p> -->
        <?php endif ?>
      </div>

    </div>
  </div>
</footer>
