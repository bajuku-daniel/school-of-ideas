<?php
/** @var \Kirby\Cms\Block $block */

$cols   = max(2, min(5, (int)$block->cols()->or(3)->value()));
$aspect = (string)$block->aspect()->or('portrait');
$cards  = $block->cards()->toStructure();

$aspectValue = match($aspect) {
    'square'    => '1 / 1',
    'landscape' => '4 / 3',
    default     => '3 / 4',
};

$attrs = soi_section_attrs($block, 'information-images', [
    '--info-images-cols'   => $cols,
    '--info-images-aspect' => $aspectValue,
]);
?>
<section<?= $attrs ?>>
  <div class="container">
    <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
      <header class="information-images__head">
        <h2 class="information-images__title">
          <?php if ($block->headlineInk()->isNotEmpty()): ?>
            <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
          <?php endif ?>
          <?php if ($block->headlineAccent()->isNotEmpty()): ?>
            <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
          <?php endif ?>
        </h2>
        <?php if ($block->sub()->isNotEmpty()): ?>
          <p class="information-images__sub"><?= soi_html($block->sub()) ?></p>
        <?php endif ?>
      </header>
    <?php endif ?>

    <div class="information-images__grid">
      <?php foreach ($cards as $card):
        $imageUrl = soi_file_url($card->image());
        $name     = (string)$card->name();
        $position = (string)$card->position();
        $url      = trim((string)$card->url());
        $tag      = $url !== '' ? 'a' : 'div';
        $hrefAttr = $url !== '' ? ' href="' . esc($url) . '"' . (str_starts_with($url, 'http') ? ' target="_blank" rel="noopener"' : '') : '';
      ?>
        <<?= $tag ?> class="information-images__card"<?= $hrefAttr ?>>
          <figure class="information-images__media">
            <?php if ($imageUrl): ?>
              <img src="<?= esc($imageUrl) ?>"<?= soi_image_focus_attr($card->image()) ?> alt="<?= esc($name) ?>">
            <?php else: ?>
              <span class="information-images__placeholder" aria-hidden="true"></span>
            <?php endif ?>
          </figure>
          <?php if ($name !== ''): ?>
            <p class="information-images__name"><?= esc($name) ?></p>
          <?php endif ?>
          <?php if ($position !== ''): ?>
            <p class="information-images__position"><?= esc($position) ?></p>
          <?php endif ?>
        </<?= $tag ?>>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
