<?php
  use_helper('Widgets');
  $sf_request->setParameter('_homeFooter', true);

  $userId = $sf_user->getUserId();

  // studyPos will be 0 if the user has no flashcards yet!
  $studyPos = ReviewsPeer::getSequencePosition($userId);
  $studyNext = $studyPos + 1;

  $sequenceName = rtkIndex::inst()->getSequenceName();
  $isSequenceComplete = $studyPos === rtkIndex::inst()->getNumCharactersVol1();

  $studyMax = rtkIndex::inst()->getNumCharactersVol1();

  // if there are no flashcards, default to 1st lesson
  $curLesson = rtkIndex::getLessonDataForIndex($studyPos ?: 1);

  $studyLesson = $curLesson['lesson_nr'];

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
  <div class="col-md-6 mb-4 md:mb-0">
    <div class="ko-Box ko-DashBox h-full">
      <h3 class="ko-DashBox-title">Study</h3>

<?php if ($isSequenceComplete): ?>
      <div class="text-smx mb-3">
        <span class="text-success-darker font-bold">Well done! <?= $sequenceName; ?> completed!</span> <?= link_to('Change', 'account/sequence', ['class' => 'ml-2']); ?>
      </div>
<?php else: ?>
      <div class="text-smx mb-3">
        <strong><?= $flashcardCount; ?></strong> / <?= $studyMax; ?> kanji in <strong><?= $sequenceName; ?></strong>
        <?= link_to('Change', 'account/sequence', ['class' => 'ml-2']); ?>
      </div>
<?php endif; ?>

      <div id="JsHomePctBar" class="mb-4"><!-- vue --></div>

      <div>

<?php if ($isSequenceComplete): ?>
<?= link_to(
  // generic study button when sequence is complete
  'Go to Study <i class="fa fa-book-open ml-2"></i>',
  url_for('@study_edit?id=1'),
  ['class' => 'ko-Btn ko-Btn--success ko-Btn--large is-ghost']
);
?>
<?php else: ?>
<?= link_to(
  'Study Kanji #'.$studyNext.'<i class="fa fa-book-open ml-2"></i>',
  $urls['study-resume-url'],
  ['class' => 'ko-Btn ko-Btn--success ko-Btn--large']
);
?>
<?php endif; ?>
<?php
  if ($restudyCount)
  {
    echo _bs_button_to(
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

<?php if (!$hasFlashcards): ?>
  <div class="flex items-start mb-4">
    <img src="/koohii/misc/home-dash-srs-no.png" alt="" width="157" height="50" class="block border border-[#42413d40] rounded-sm"/>
    <div class="text-smx ml-4">
      <strong>Spaced Repetition</strong> will be available after you <?= link_to('add kanji flashcards', '@manage', ['class' => 'whitespace-nowrap']); ?>.
    </div> 
  </div>
<?php endif; ?>


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

        <?= _bs_button_to(
  'Spaced Repetition'.'<i class="fa fa-arrow-right ml-2"></i>',
  '@overview',
  [
    'class' => 'ko-Btn ko-Btn--primary ko-Btn--large ml-auto',
  ]
);
        ?>
      </div>
<?php endif; ?>

      <div class="bg-dash-line h-px mb-3"></div>

      <p class="text-smx mb-2"><strong>Custom Review</strong>. Does not use the SRS. <?= link_to('Learn More', '@learnmore#custom-review', ['class' => 'ml-2 whitespace-nowrap']); ?></p>

      <?= link_to('Review by Index or Lesson'.'<i class="fa fa-arrow-right ml-2"></i>', 'review/custom', ['class' => 'ko-Btn ko-Btn--primary ko-Btn--small ml-auto']); ?>

    </div>
  </div>
</div>

<div id="JsHomeLesson" class="mb-4"><!-- vue --></div>

<?php /* PURPOSELY CLOSE THE MAIN CONTAINER cf. layout.php
  </div><!-- /#main_container -->
</div><!-- /#main -->
<div id="main">
  <div id="main_container" class="container">
 */ ?>

<?php
  include_partial('news/recent');

  // ids of kanji cards shown in this lesson (to limit queries)
  $cardsIds = rtkIndex::createFlashcardSet(
    $curLesson['lesson_from'],
    $curLesson['lesson_from'] + $curLesson['lesson_count'] - 1
  );

  // include orig & user keyword maps for the kanji card component
  rtkIndex::useKeywordsFile();

  $keywordsMap = CustkeywordsPeer::getUserKeywordsMap($userId, $cardsIds);

  $pctBarProps = [
    'value' => $flashcardCount,
    'max-value' => $studyMax,
  ];

  $cardsData = ReviewsPeer::getJsKanjiCards($userId, $cardsIds);
  $lessonProps = [
    'cards' => $cardsData,
    'lessonNum' => $curLesson['lesson_nr'],
    'lessonPos' => $curLesson['lesson_pos'],
    'lessonCount' => $curLesson['lesson_count'],
    'allLessonsCount' => $numLessons,
    'allLessonsUrl' => url_for('@progress'),
    'sequenceName' => $sequenceName,
    'maxHeight' => true,
  ];

  kk_globals_put('USER_KEYWORDS_MAP', $keywordsMap);
  kk_globals_put('HOMEDASH_PCTBAR_PROPS', $pctBarProps);
  kk_globals_put('HOMEDASH_LESSON_PROPS', $lessonProps);
