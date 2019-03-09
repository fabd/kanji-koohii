<?php
  use_helper('Widgets');
  $sf_request->setParameter('_homeFooter', true);
?>
  

<div id="home-qs" class="row mt-1 mb-2">
 
 <!-- temp fix for wide blog posts until redesign of home screen -->
 <div class="col-md-10 col-md-push-1">
  <h2>Welcome back, <?php echo $sf_user->getUserName() ?>!</h2>
  <div class="row">


  <div class="col-md-4">
    <div class="box padded-box-inset mb-p50">
    <div class="hd">
<?php 
      if ($countFailed<=0) {
        echo 'No restudy '._CJ('kanji');
      } else {
        echo '<strong>'.$countFailed.'</strong> '.link_to('study '._CJ('kanji'), 'study/failedlist', array(/*'query_string' => 'mode=failed',*/ 'class' => 'failed', 'title' => 'Restudy forgotten '._CJ('kanji')));
      }
?>
    </div>
    <div class="bd">
      <?php echo _bs_button('Study','study/index', array('class' => 'btn btn-lg btn-go' )) ?>
    </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="box padded-box-inset mb-p50">
    <div class="hd">
<?php 
      if ($countExpired<=0) {
        echo 'No expired '._CJ('kanji');
      } else {
        echo '<strong>'.$countExpired.'</strong> '.link_to('expired '._CJ('kanji'), '@review', array('class' => 'expired', 'query_string' => 'type=expired&box=all', 'title' => 'Review expired '._CJ('kanji')));
      }
?>
    </div>
    <div class="bd">
      <?php echo _bs_button('Review','@overview', array('class' => 'btn btn-lg btn-go' )) ?>
    </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="box padded-box-inset mb-p50">
    <div class="hd">
<?php 
      if ($progress->heisignum === false) {
        // kanji added out of order, display count of flashcards in RTK1-3
        $count = ReviewsPeer::getFlashcardCount($sf_user->getUserId());
        echo '<strong>'.$count.' of '.rtkIndex::inst()->getNumCharactersVol3().'</strong> flashcards';
      } 
      elseif ($progress->heisignum < rtkIndex::inst()->getNumCharactersVol1()) {
        echo 'Lesson <strong>'.$progress->curlesson.' of 56</strong>'; //<br />'.$progress->kanjitogo.' '._CJ('kanji').' to go';
      }
      else {
        echo 'RTK 1 completed!';
      }
?>
    </div>
    <div class="bd">
      <?php echo _bs_button('Progress chart','@progress', array('class' => 'btn btn-lg btn-go' )) ?>
    </div>
    </div>
  </div>

 </div></div><!-- /col-md-10 fix -->

</div>


    <?php include_partial('news/recent') ?>


