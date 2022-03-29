<?php
  $userId = $sf_user->getUserId();
  $restudyCount = ReviewsPeer::getRestudyKanjiCount($userId);
  $learnedCount = LearnedKanjiPeer::getCount($userId);
?>

<h2>Restudy List</h2>

<div class="row md:items-stretch mb-8">
  <div class="col-md-6 mb-4 md:mb-0">
<?php if ($restudyCount): ?>
    <div class="ko-Box ko-Box--stroke ko-Box--danger md:h-full">
      <h3 class="text-md font-bold text-danger-dark"><?= $restudyCount; ?> Kanji to Restudy</h3>
<?php else: ?>
    <div class="ko-Box ko-Box--success">
      <h3 class="text-md font-bold text-success-darker">No Forgotten Kanji</h3>
<?php endif; ?>

      <p>Restudy your forgotten kanji, <em>in index order</em>. <?= link_to('Learn More', '@learnmore#restudy-list', ['class' => 'whitespace-nowrap']); ?>

      <div class="flex items-center">
<?php
      $restudyLabel = $learnedCount ? 'Continue Restudy' : 'Begin Restudy';
      // also disable when all are learned!
      if ($restudyCount && $learnedCount < $restudyCount)
      {
        echo _bs_button(
          $restudyLabel.'<i class="fa fa-book-open ml-2"></i>',
          'study/edit',
          ['query_string' => 'restudy', 'class' => 'ko-Btn ko-Btn--danger ko-Btn--large']
        );
      }
      else
      {
        echo _bs_button(
          'Begin Restudy<i class="fa fa-book-open ml-2"></i>',
          '@review',
          [
            'query_string' => 'box=1',
            'class' => 'ko-Btn ko-Btn--danger ko-Btn--large is-disabled',
            'disabled' => true,
          ]
        );
      }
?>
<?php
      // don't show if all are learned (less confusing)
      if ($restudyCount && $restudyCount > $learnedCount)
      {
        echo _bs_button(
          'Review All<i class="fa fa-arrow-right ml-2"></i>',
          '@review',
          ['query_string' => 'box=1', 'class' => 'ko-Btn ko-Btn--danger ko-Btn--large is-ghost ml-4']
        );
      }
?>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="ko-Box md:h-full">

      <div class="flex">
        <h3 class="text-md font-bold text-body">Learned Kanji</h3>
<?php if ($learnedCount): ?>
          <?= link_to(
  '<i class="fa fa-times mr-2"></i>Clear learned list ',
  'study/clear?goto=restudy',
  ['class' => 'leading-1 text-danger-darker hover:underline ml-auto']
); ?>
<?php endif; ?>
      </div>
      
<?php if ($learnedCount): ?>
        <p>You have learned kanji ready for review!</p>
<?php else: ?>
        <p>No learned kanji to review.</p>
<?php endif; ?>
<?php
      if ($learnedCount)
      {
        echo _bs_button(
          "Review <strong>{$learnedCount}</strong> Learned Kanji".'<i class="fa fa-arrow-right ml-2"></i>',
          '@review',
          [
            'query_string' => 'type=relearned',
            'class' => 'ko-Btn ko-Btn--success ko-Btn--large',
          ]
        );
      }
      else {
        echo _bs_button(
          'Review Learned Kanji<i class="fa fa-arrow-right ml-2"></i>',
          '@review',
          [
            'query_string' => 'type=relearned',
            'class' => 'ko-Btn ko-Btn--success ko-Btn--large is-disabled',
            'disabled' => true,
          ]
        );
      }
?>

    </div>
  </div>

</div>

<?php if (!$restudyCount): ?>

  <div class="ko-Box  min-h-[336px] flex mb-8">
    <p class="text-warm m-auto">
      Hooray! Your restudy list is empty!
    </p>
  </div>

<?php else: ?>

  <?php include_component('study', 'FailedListTable'); ?>

<?php endif; ?>
