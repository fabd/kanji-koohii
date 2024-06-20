<?php
  use_helper('Gadgets');
  $sf_request->setParameter('_homeFooter', true);

  $userId = $sf_user->getUserId();

  $keywordsMap = CustkeywordsPeer::getUserKeywordsMapJS($userId);

  $cardsData = ReviewsPeer::getUserKanjiCardsJS($userId);
// DBG::printr($cardsData);exit;

  // include orig & user keyword maps for the kanji card component
  rtkIndex::useKeywordsFile();

  kk_globals_put([
    'USER_KEYWORDS_MAP' => $keywordsMap,
    'USER_KANJI_CARDS' => $cardsData
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
