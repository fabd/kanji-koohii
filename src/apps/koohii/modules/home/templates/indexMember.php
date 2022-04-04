<?php
  use_helper('Widgets');
  $sf_request->setParameter('_homeFooter', true);

  // optimze?
  // $carddata = ReviewsPeer::getLeitnerBoxCounts($this->filter);
  // $this->restudy_cards = $carddata[0]['expired_cards'];

  // alias template vars here, in case this becomes a Vue comp. someday
  $rtk = rtkIndex::inst();
  $studyPos = $progress->heisignum ?: 0;
  $studyNext = $studyPos + 1;
  $studyMax = rtkIndex::inst()->getNumCharactersVol1();
  $studyLesson = $progress->curlesson ?: 1;
  $studyLessonMax = rtkIndex::inst()->getCharCountForLesson($studyLesson);
  $studyLessonPos = $studyLessonMax - $progress->kanjitogo;

  $studyButtonLabel = $studyPos < $studyMax
    ? 'Study Kanji #'.$studyNext
    : 'Study Kanji #'.$studyNext;

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

  $urls = [
    'study-resume-url' => url_for('@study_edit?'.http_build_query(['id' => $studyNext])),
    'new' => url_for_review(['type' => 'untested']),
    'due' => url_for_review(['type' => 'expired']),
  ];

// DBG::user();
?>

<h2>Welcome back, <?= $sf_user->getUserName(); ?>!</h2>

<div class="row mb-5">
  <div class="col-md-6 md:mb-0">
    <div class="ko-Box ko-DashBox">
      <h3 class="ko-DashBox-title">Study</h3>

      <div class="text-smx mb-3">
        <strong><?= $flashcardCount; ?></strong> / <?= $studyMax; ?> kanji in <strong><?= $rtk->getSequenceName(); ?></strong>
        <?= link_to('Change', 'account/sequence', ['class' => 'ml-2']); ?>
      </div>

      <div id="JsDashboardPctBar" class="mb-4"><!-- vue --></div>

      <div>
<?= link_to(
    $studyButtonLabel.'<i class="fa fa-book-open ml-2"></i>',
    $urls['study-resume-url'],
    ['class' => 'ko-Btn ko-Btn--success ko-Btn--large']);
?>
<?php
  if ($restudyCount)
  {
    echo _bs_button(
      "{$restudyCount} Kanji to Restudy".'<i class="fa fa-book-open ml-2"></i>',
      'study/failedlist',
      [
        'class' => 'ko-Btn ko-Btn--danger ko-Btn--large ml-4',
      ]
    );
  }
?>
      </div>

    </div>
  </div>

  <div class="col-md-6 md:mb-0">
    <div class="ko-Box ko-DashBox h-full">
      <h3 class="ko-DashBox-title">Review</h3>

<?php if ($hasFlashcards): ?>
      <div class="flex items-stretch -ml-2 mb-4">
        <a class="ko-Dash-srsIcoBtn is-new flex items-center" href="<?= $urls['new']; ?>" title="Review new kanji cards">
          <div class="ko-Dash-srsIso is-new"><em class="is-top"></em><em class="is-side"></em></div>
          <span class="ml-2"><?= $countSrsNew; ?> <strong>new</strong></span>
        </a>

        <a class="ko-Dash-srsIcoBtn is-due flex items-center ml-2" href="<?= $urls['due']; ?>" title="Review due kanji cards">
          <div class="ko-Dash-srsIso is-due"><em class="is-top"></em><em class="is-side"></em></div>
          <span class="ml-2"><?= $countSrsDue; ?> <strong>due</strong></span>
        </a>

        <?= _bs_button(
  'Spaced Repetition'.'<i class="fa fa-arrow-right ml-2"></i>',
  '@overview',
  [
    'class' => 'ko-Btn ko-Btn--primary ko-Btn--large ml-auto',
  ]
);
        ?>
      </div>
<?php endif; ?>

      <div class="bg-[#c2bdaf] h-px mb-4"></div>

    </div>
  </div>
</div>

<div class="ko-Box ko-DashBox">
  <h3 class="ko-DashBox-title"><?= $rtk->getSequenceName(); ?> - Lesson <?= $studyLesson; ?></h3>

  <div>
<?php
  if ($studyPos < $studyMax)
  {
    echo "{$studyLessonPos} / {$studyLessonMax} in <strong>lesson {$studyLesson}</strong>";
  }
  else
  {
    echo 'RTK 1 completed!';
  }
?>
  
  <?= link_to("Show all {$numLessons} lessons", '@progress', ['class' => 'ml-2']); ?>
  </div>

</div>
<?php
  include_partial('news/recent');

  $propsPctBar = [
    'value' => $flashcardCount,
    'max-value' => $studyMax,
  ];

  kk_globals_put('HOMEDASH_PCTBAR_PROPS', $propsPctBar);
