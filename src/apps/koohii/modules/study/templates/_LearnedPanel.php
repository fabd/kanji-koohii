<?php
  $restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());

  $ofTotal = $restudyCount > 0 ? " of {$restudyCount}" : '';
?>
<div class="ko-Box ko-Box--success no-gutter-xs-sm dsk:mb-4 mbl:rounded-none">
  <div class="flex items-center mb-2">
    <h3 class="text-success-dark font-bold leading-1 mb-0">Learned</h3>
    <?= link_to('List', 'study/failedlist', ['class' => 'text-sm leading-1 ml-auto mbl:ml-4']); ?>
  </div>
  <div class="mbl:flex flex-wrap items-center justify-between">
    <p class="text-success-darker text-sm mb-2 mbl:mb-0">
      <strong><?= $learnedCount; ?></strong>
      of
      <strong><?= $restudyCount; ?></strong> Forgotten Kanji
    </p>
<?php
      if ($learnedCount)
      {
        echo _bs_button(
          'Review Learned'.'<i class="fa fa-arrow-right ml-2"></i>',
          '@review',
          [
            'query_string' => 'type=relearned',
            'class' => 'ko-Btn ko-Btn--success',
          ]
        );
      }
      else
      {
        echo _bs_button(
          'Review Learned<i class="fa fa-arrow-right ml-2"></i>',
          '@review',
          [
            'query_string' => 'type=relearned',
            'class' => 'ko-Btn ko-Btn--success is-disabled',
            'disabled' => true,
          ]
        );
      }
?>
  </div>
</div>
