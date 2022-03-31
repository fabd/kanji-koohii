<?php
  use_helper('Widgets');
  $sf_request->setParameter('_homeFooter', true);

  // optimze?
  // $carddata = ReviewsPeer::getLeitnerBoxCounts($this->filter);
  // $this->restudy_cards = $carddata[0]['expired_cards'];

  // alias template vars here, in case this becomes a Vue comp. someday
  $rtk = rtkIndex::inst();
  $studyPos = $progress->heisignum ?: 1;
  $studyMax = rtkIndex::inst()->getNumCharactersVol1();
  $studyLesson = $progress->curlesson ?: 1;
  $studyLessonMax = rtkIndex::inst()->getCharCountForLesson($studyLesson);
  $studyLessonPos = $studyLessonMax - $progress->kanjitogo;
  
  // FIXME - shows Restudy across ALL cards - not just current sequence
  $restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());

  // number of lessons in current sequence
  $numLessons = rtkIndex::inst()->getNumLessonsVol1();

  // count of flashcards part of current  sequence
  $flashcardCount = ReviewsPeer::getFlashcardCount($sf_user->getUserId(), 'rtk1');

  // is the SRS active? (*ANY* flashcards, not just current sequence)
  $hasFlashcards = ReviewsPeer::getFlashcardCount($sf_user->getUserId());
  $countSrsNew = $hasFlashcards ? ReviewsPeer::getCountUntested($sf_user->getUserId()) : 0;
  $countSrsDue = $hasFlashcards ? ReviewsPeer::getCountExpired($sf_user->getUserId()) : 0;

// DBG::user();
?>

<h2>Welcome back, <?= $sf_user->getUserName(); ?>!</h2>

<div class="row mb-5">
  <div class="col-md-6 md:mb-0">
    <div class="ko-Box ko-DashBox">
      <h3 class="ko-DashBox-title">Study</h3>

      <div class="text-smx mb-4">
        <strong><?= $flashcardCount; ?></strong> / <?= $studyMax; ?> kanji in <strong><?= $rtk->getSequenceName(); ?></strong>
        <?= link_to('Change', 'account/sequence', ['class' => 'ml-2']); ?>
      </div>

      <div class="kk-PctBar mb-4">
        27%
      </div>

      <div>
<?= _bs_button('Study Kanji #1<i class="fa fa-book-open ml-2"></i>', 'study/index', ['class' => 'ko-Btn ko-Btn--success ko-Btn--large']); ?>
<?php if ($restudyCount)
{
  echo _bs_button(
    "Restudy List ( {$restudyCount} )".'<i class="fa fa-book-open ml-2"></i>',
    'study/failedlist',
    [
      'class' => 'ko-Btn ko-Btn--danger ko-Btn--large ml-4',
    ]
  );
} ?>
      </div>

    </div>
  </div>

  <div class="col-md-6 md:mb-0">
    <div class="ko-Box ko-DashBox h-full">
      <h3 class="ko-DashBox-title">Review</h3>

<?php if ($hasFlashcards): ?>
      <div class="flex items-center">
        <div class="flex items-center">
          <div class="kk-DocIso is-new text-[11px]"><em class="is-top"></em><em class="is-side"></em></div>
          <span class="ml-4"><?= $countSrsNew ?> <strong>new</strong></span>
        </div>
        <div class="flex items-center ml-4">
          <div class="kk-DocIso is-due text-[11px]"><em class="is-top"></em><em class="is-side"></em></div>
          <span class="ml-4"><?= $countSrsDue ?> <strong>due</strong></span>
        </div>
        <?php
        echo _bs_button(
          "Spaced Repetition".'<i class="fa fa-arrow-right ml-2"></i>',
          '@overview',
          [
            'class' => 'ko-Btn ko-Btn--primary ko-Btn--large ml-auto',
          ]
        );
        ?>
      </div>
<?php endif; ?>  

    </div>
  </div>
</div>

<div class="ko-Box ko-DashBox">
  <h3 class="ko-DashBox-title"><?= $rtk->getSequenceName(); ?> - Lesson <?= $studyLesson ?></h3>

  <div>
<?php if ($studyPos < $studyMax)
      {
        echo "{$studyLessonPos} / {$studyLessonMax} in <strong>lesson {$studyLesson}</strong>";
      }
      else
      {
        echo 'RTK 1 completed!';
      }
?>
  
  <?= link_to("Show all {$numLessons} lessons", '@progress', ['class' => 'ml-2' ]); ?>
  </div>

</div>

<?php include_partial('news/recent'); ?>
