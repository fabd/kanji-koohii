<?php
  // required for displaying Cust Keywords in "Last Viewed" component
  $userId = $sf_user->getUserId();
  $keywordsMap = CustkeywordsPeer::getUserKeywordsMapJS($userId);
  kk_globals_put('USER_KEYWORDS_MAP', $keywordsMap);
?>
<div class="row">

<?php include_partial('SideColumn', [
  'kanjiData' => false,
  'isBeginRestudy' => false
  ]) ?>

  <div class="col-lg-9">
    <?php include_partial('study/StudyIntro') ?>
  </div>

</div>