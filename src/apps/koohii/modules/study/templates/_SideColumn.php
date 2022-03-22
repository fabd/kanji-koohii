<?php
  use_helper('CJK', 'Form');
  
  // file hash matches the ones output by Vite build (cf .htaccess rule)
  $FILEHASH = '20211019';
  $rtkIndex = $sf_user->getUserSequence(); // 0 = OLD, 1 = NEW
  $keywordsFile = "/revtk/study/keywords-rtk-{$rtkIndex}.{$FILEHASH}.js";
  use_javascript($keywordsFile, 'first', ['defer' => true]);

  //$restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());

  $framenum = $kanjiData ? $kanjiData->framenum : 0;
  // $ucs_code = $kanjiData ? $kanjiData->ucs_id : 0;

  $cur_kanji = $kanjiData ? $kanjiData->kanji : '1';
?>

<div class="col-lg-3 mb-3">
<?php 
  include_partial('study/StudySearch', ['framenum' => $framenum]);

  //FIXME : optimizer avec une cache, on cache le learnedcount !!
  $learnedCount = LearnedKanjiPeer::getCount($sf_user->getUserId());

  if ($sf_user->hasAttribute(rtkUser::IS_RESTUDY_SESSION) || $learnedCount > 0) {
    include_partial('study/LearnedPanel', ['learnedCount' => $learnedCount, 'kanji' => $cur_kanji]);
  }
  else if ($restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId())) {
    include_partial('RestudyList', ['restudy_count' => $restudyCount, 'kanji' => $cur_kanji]);
  }

?>

  <div class="visible-md-lg ko-Box mb-3">
    <h3>Links</h3>
    <div class="mb-2"><?php echo link_to('My stories','study/mystories') ?></div>
  </div>
  

</div><!-- /col -->

<?php kk_globals_put('STUDY_SEARCH_URL', url_for('study/kanji', true)) ?>