<?php
  use_helper('CJK', 'Form');

  // keywords and index for the search box (front end side)
  rtkIndex::useKeywordsFile();

  //$restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());

  $framenum = $kanjiData ? $kanjiData->framenum : 0;
  // $ucs_code = $kanjiData ? $kanjiData->ucs_id : 0;

  $cur_kanji = $kanjiData ? $kanjiData->kanji : '1';
?>

<div class="col-lg-3 mb-3">
<?php
  include_partial('study/StudySearch', ['framenum' => $framenum]);

  $restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());
  $learnedCount = LearnedKanjiPeer::getCount($sf_user->getUserId());

  if ($isBeginRestudy || $learnedCount > 0)
  {
    include_partial('LearnedPanel', [
      'learnedCount' => $learnedCount,
      'restudyCount' => $restudyCount,
      'kanji' => $cur_kanji,
    ]);
  }
  elseif ($restudyCount)
  {
    include_partial('RestudyPanel', [
      'restudyCount' => $restudyCount,
    ]);
  }
?>

  <div class="max-lg:hidden ko-Box mb-4">
    <h3>Links</h3>
    <div class="mb-2"><?= link_to('My stories', 'study/mystories'); ?></div>
  </div>
  
  <?php /* (fabd) for now prefer to add JS widgets last in the sidebar, so it
  doesn't visually flicker other static content that would come below it, if JS
  was slow to load */ ?>
  <div id="JsLastViewedKanji" class="max-lg:hidden mb-4"></div>

</div><!-- /col -->

<?php kk_globals_put('STUDY_SEARCH_URL', url_for('study/kanji', true)); ?>