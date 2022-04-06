<?php
  use_helper('Widgets');
  $sf_request->setParameter('_homeFooter', true);

  $userId = $sf_user->getUserId();

  $studyPos = ReviewsPeer::getSequencePosition($userId);
  $studyNext = $studyPos + 1;

  $sequenceName = rtkIndex::inst()->getSequenceName();
  $isSequenceComplete = $studyPos === rtkIndex::inst()->getNumCharactersVol1();

  $studyMax = rtkIndex::inst()->getNumCharactersVol1();

  $curLesson = rtkIndex::getLessonDataForIndex($studyNext);
  $curLessonOffset = $curLesson ? $studyPos - $curLesson['lesson_from'] + 1 : 0;

  $studyLesson = $curLesson['lesson_nr'] ?: 1;

  $studyButtonLabel = $studyPos < $studyMax
    ? 'Study Kanji #'.$studyNext
    : 'Study Kanji #'.$studyNext;

  // FIXME - shows Restudy across ALL cards - not just current sequence
  $restudyCount = ReviewsPeer::getRestudyKanjiCount($userId);

  $numLessons = rtkIndex::inst()->getNumLessonsVol1();

  // count of flashcards part of current  sequence
  $flashcardCount = ReviewsPeer::getFlashcardCount($userId, 'rtk1');

  // is the SRS active? (*ANY* flashcards, not just current sequence)
  $hasFlashcards = ReviewsPeer::getFlashcardCount($userId);
  $countSrsNew = $hasFlashcards ? ReviewsPeer::getCountUntested($userId) : 0;
  $countSrsDue = $hasFlashcards ? ReviewsPeer::getCountExpired($userId) : 0;

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
        <strong><?= $flashcardCount; ?></strong> / <?= $studyMax; ?> kanji in <strong><?= $sequenceName; ?></strong>
        <?= link_to('Change', 'account/sequence', ['class' => 'ml-2']); ?>
      </div>

      <div id="JsDashboardPctBar" class="mb-4"><!-- vue --></div>

      <div>
<?= link_to(
  $studyButtonLabel.'<i class="fa fa-book-open ml-2"></i>',
  $urls['study-resume-url'],
  ['class' => 'ko-Btn ko-Btn--success ko-Btn--large']
);
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

      <div class="bg-[#c2bdaf] h-px mb-3"></div>

      <p class="text-smx mb-2"><strong>Custom Review</strong>. Does not use the SRS. <?= link_to('Learn More', '@learnmore#custom-review', ['class' => 'ml-2 whitespace-nowrap']); ?></p>

      <?= link_to('Review by Index or Lesson'.'<i class="fa fa-arrow-right ml-2"></i>', '@overview', ['class' => 'ko-Btn ko-Btn--primary ko-Btn--small ml-auto']); ?>

    </div>
  </div>
</div>

<div class="ko-Box ko-DashBox">
  <h3 class="ko-DashBox-title">Lesson <?= $curLesson['lesson_nr']; ?><span class="font-normal"> in <?= $sequenceName; ?></span></h3>

  <div>
<?php
  if (!$isSequenceComplete)
  {
    echo "{$curLessonOffset} / {$curLesson['lesson_count']} in <strong>lesson {$curLesson['lesson_nr']}</strong>";
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
