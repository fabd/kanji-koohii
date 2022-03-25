<?php
  $restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());

  $ofTotal = $restudyCount > 0 ? " of {$restudyCount}" : '';
?>
<div class="ko-Box ko-Box--success ko-Box--stroke no-gutter-xs-sm dsk:mb-4">
  <h3 class="text-success-dark font-bold leading-1 mb-2">Learned</h3>
  <p class="text-success-darker text-sm mb-2">
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
