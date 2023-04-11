<?php
  use_helper('Gadgets');
  $sf_request->setParameter('_homeFooter', true);

  $userId = $sf_user->getUserId();

  // prep lessons chart data -- EXCLUDE RTK 3 for now (in the future
  //  it will be a separate sequence -- this to avoid dumping 800 kanji card
  //  in one lesson)
  $rtkLessons = rtkIndex::getLessons();
  $lessons = [];
  foreach ($rtkLessons as $id => $count)
  {
    // for now ignore  RTK 3
    if ($id === 57)
    {
      continue;
    }

    $lessData = rtkIndex::getLessonData($id);

    $lessons[] = [
      'id' => $lessData['lesson_nr'],
      'from' => $lessData['lesson_from'],
      'count' => $lessData['lesson_count'],
    ];
  }
// DBG::printr($lessons);exit;

  $keywordsMap = CustkeywordsPeer::getUserKeywordsMapJS($userId);

  $cardsData = ReviewsPeer::getUserKanjiCardsJS($userId);
// DBG::printr($cardsData);exit;
  $sequenceName = rtkIndex::inst()->getSequenceName();

  $lessonsChartProps = [
    'lessons' => $lessons,
    'sequenceName' => $sequenceName,
  ];

  // include orig & user keyword maps for the kanji card component
  rtkIndex::useKeywordsFile();

  kk_globals_put([
    'USER_KEYWORDS_MAP' => $keywordsMap,
    'USER_KANJI_CARDS' => $cardsData,
    'LESSONS_CHART_PROPS' => $lessonsChartProps
  ]);
?>
<h2>View All Lessons</h2>

<p>
Here you can explore all lessons in <strong><?= $sequenceName; ?></strong> - as well as check your overall progress.
</p>

<p class="text-[#cc2d7a] mb-4">
  <i class="fas fa-info-circle mr-2"></i>
  Your progress through lessons is tracked by <?= link_to('adding flashcards', '@manage'); ?> (reviewing them is optional).
</p>

<div class="h-4"></div>

<?php if (isset($extraFlashcards)): ?>
  <div class="warningmessagebox">
    Note: <?= $extraFlashcards->total; ?> flashcards in your deck which are not part of <strong><?= rtkIndex::inst()->getSequenceName(); ?></strong>
    are ignored in the chart.
  </div>
<?php endif; ?>

<div id="JsViewAllLessons" class="ko-ViewAllLessons mb-4">
  <!-- vue -->
</div>
