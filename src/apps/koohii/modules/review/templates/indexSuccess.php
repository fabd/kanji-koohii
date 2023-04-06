<?php
  $todayCount = ReviewsPeer::getTodayCount($sf_user->getUserId());
  ?>

<div class="flex items-center mb-4">
  <h2 class="mb-0">Spaced Repetition</h2>

  <?= link_to(
    '<i class="fa fa-edit mr-2"></i>SRS Settings',
    'account/spacedrepetition',
    ['class' => 'uiGUI ko-Btn is-ghost ml-auto']
  ); ?>
</div>

<div class="text-lg mb-2">
  <span class="mr-4"><strong><?= $flashcard_count; ?></strong> flashcards</span>
  <span class="text-[#484] mr-4"><strong><?= $todayCount; ?></strong> reviews today</span>
</div>

<div class="mb-8">
  <div id="view-pane-all" class="rtk-filter-pane">
    <?php include_component('review', 'LeitnerChart'); ?>
  </div>
</div>

<h3>Due cards next week</h3>
<p>The following bar chart represents how many cards are scheduled for review over the next week.</p>
<?php include_component('review', 'DueCardsGraph'); ?>
