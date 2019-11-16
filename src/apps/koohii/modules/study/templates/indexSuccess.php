<?php
  $this->latestKanji = ReviewsPeer::getMaximumSequenceNumber($sf_user->getUserId());
?>
<div class="row">

  <?php include_partial('SideColumn', array('kanjiData' => false, 'intro' => true /* <- could be "false" */ )) ?>

  <div class="col-md-9">
    <?php include_partial('study/StudyIntro', array('latestKanji' => $this->latestKanji)) ?>
  </div>

</div>