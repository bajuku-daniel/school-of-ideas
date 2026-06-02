<?php
/** @var \Kirby\Cms\Block $block */

$attrs  = soi_section_attrs($block, 'qa');
$groups = $block->groups()->toStructure();
?>
<section<?= $attrs ?>>
  <div class="container">
    <?php if ($block->headlineInk()->isNotEmpty() || $block->headlineAccent()->isNotEmpty()): ?>
    <header class="qa__head">
      <h2 class="qa__lead">
        <span class="ink"><?= soi_html($block->headlineInk()) ?></span>
        <?php if ($block->headlineAccent()->isNotEmpty()): ?>
          <span class="accent"><?= soi_html($block->headlineAccent()) ?></span>
        <?php endif ?>
      </h2>
    </header>
    <?php endif ?>

    <div class="qa__groups">
      <?php foreach ($groups as $gi => $group):
        $questions = $group->questions()->toStructure();
        if ($questions->isEmpty()) continue;
      ?>
        <div class="qa__group">
          <?php if ($group->title()->isNotEmpty()): ?>
            <h3 class="qa__group-title"><?= soi_html($group->title()) ?></h3>
          <?php endif ?>
          <div class="qa__items">
            <?php foreach ($questions as $qi => $q):
              $isFirstOfFirstGroup = ($gi === 0 && $qi === 0);
            ?>
              <details class="qa__item"<?= $isFirstOfFirstGroup ? ' open' : '' ?>>
                <summary class="qa__question">
                  <span class="qa__question-text"><?= soi_html($q->question()) ?></span>
                  <span class="qa__toggle" aria-hidden="true"></span>
                </summary>
                <div class="qa__answer"><?= soi_paragraphs($q->answer()) ?></div>
              </details>
            <?php endforeach ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
  <?= soi_section_icon_at($block, 'section') ?>
</section>
