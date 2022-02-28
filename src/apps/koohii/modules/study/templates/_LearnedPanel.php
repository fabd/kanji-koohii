<?php 
  // the restudy session flag is also a cache of the count of failed cards
  $failedCount = $sf_user->getAttribute(rtkUser::IS_RESTUDY_SESSION, 0);
  $ofTotal = $failedCount > 0 ? " of $failedCount" : '';
?>
<div id="JsLearnedComponent">
<div id="study-learned" class="study-action-comp no-gutter-xs-sm md:mb-4">

  <div class="flex flex-g-s">

    <div class="col-m col-d-12 col-g self-center">
      <h3>Learned <span><?php echo $learnedCount ?></span><?php echo $ofTotal ?></h3>
    </div>

    <div class="col-m-1 col-d-6 col-g">
      <?php echo link_to('Clear', 'study/clear?goto='.$kanji, ['class' => 'btn btn-danger']) ?>
    </div>
<?php if ($learnedCount> 0): ?>
    <div class="col-m-1 col-d-6 col-g">
      <?php echo link_to('Review', '@review', ['query_string' => 'type=relearned', 'class' => 'btn btn-success']) ?>
    </div>
<?php endif ?>

  </div>
</div>
</div>