<?php
  use_helper('CJK', 'Form');
  
  use_javascript('/revtk/study/keywords-'.CJ_MODE.'-'.$sf_user->getUserSequence().'.js');

  //$restudyCount = ReviewsPeer::getRestudyKanjiCount($sf_user->getUserId());

  $framenum = $kanjiData ? $kanjiData->framenum : 0;
  // $ucs_code = $kanjiData ? $kanjiData->ucs_id : 0;

  $cur_kanji = $kanjiData ? $kanjiData->kanji : '1';
?>

<div class="col-md-3 mb-1">
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

  <div class="visible-md-lg padded-box-inset mb-1">
    <h3>Links</h3>
    <div class="mb-p50"><?php echo link_to('My stories','study/mystories') ?></div>
  </div>
  

</div><!-- /col -->

<?php koohii_onload_slot() ?>
Core.ready(function(){
  App.StudyPage.actb_extracols = function(iRow) {
    return '<span class="f">'+(iRow+1)+'</span><span <?php echo cjk_lang_attrs(['k']) ?>>&#'+kklist.charCodeAt(iRow)+';</span>';
  };

  App.StudyPage.initialize({
    URL_SEARCH:        "<?php echo url_for('study/kanji', true) ?>"
  });
});
<?php end_slot() ?>

