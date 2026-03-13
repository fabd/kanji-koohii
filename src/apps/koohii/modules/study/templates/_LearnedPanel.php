<?php
  $ofTotal = $restudyCount > 0 ? " of {$restudyCount}" : '';
?>
<div class="ko-Box ko-Box--success lg:mb-4 max-lg:flex max-lg:items-center max-lg:px-2 max-lg:py-2">
  <div class="lg:mb-2">
    <h3 class="text-[#3a7c3a] font-bold leading-none inline-block mb-0">Learned</h3>
    <?= link_to('List', 'study/failedlist', ['class' => 'text-sm leading-none  ml-2 max-lg:ml-4']); ?>
    <div class="text-[#2C892C] text-sm">
      <strong><?= $learnedCount; ?></strong>
      of
      <strong><?= $restudyCount; ?></strong> Forgotten Kanji
    </div>
  </div>
  <div class="ml-auto">
<?php
      if ($learnedCount)
      {
        echo _bs_button_to(
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
        echo _bs_button_to(
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
