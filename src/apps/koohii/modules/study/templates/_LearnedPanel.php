<?php
  $restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());

  $ofTotal = $restudyCount > 0 ? " of {$restudyCount}" : '';
?>
<div id="study-learned" class="study-action-comp no-gutter-xs-sm dsk:mb-4">

  <div class="mbl:flex items-center">

    <h3 class="m-0 dsk:mb-2">Learned <strong><?= $learnedCount; ?></strong><?= $ofTotal; ?></h3>

    <div class="flex items-center -mx-1 mbl:w-[182px] mbl:ml-auto dsk:mb-2">
      <div class="w-1/2 mx-1">
        <?= link_to('Clear', 'study/clear?goto='.$kanji, ['class' => 'ko-Btn ko-Btn--danger']); ?>
      </div>
  
      <div class="w-1/2 mx-1 mbl:ml-4">
<?php if ($learnedCount > 0): ?>
        <?= link_to('Review', '@review', ['query_string' => 'type=relearned', 'class' => 'ko-Btn ko-Btn--success']); ?>
<?php endif; ?>
      </div>

    </div>
  </div>
</div>