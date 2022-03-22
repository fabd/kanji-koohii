<?php
  use_helper('Widgets');
  $sf_request->setParameter('_homeFooter', true);
?>

<div id="home-qs" class="row mt-3 mb-8">

  <div class="col-lg-10 mx-auto">
    <h2>Welcome back, <?= $sf_user->getUserName(); ?>!</h2>
    
    <div class="row">

      <div class="col-lg-4">
        <div class="box ko-Box mb-2">
          <div class="hd">
<?php if ($countFailed <= 0)
{
  echo 'No restudy '._CJ('kanji');
}
else
{
  echo '<strong>'.$countFailed.'</strong> '.link_to('study '._CJ('kanji'), 'study/failedlist', [/*'query_string' => 'mode=failed',*/ 'class' => 'failed', 'title' => 'Restudy forgotten '._CJ('kanji')]);
}
?>
          </div>
          <div class="bd">
            <?= _bs_button('Study', 'study/index', ['class' => 'ko-Btn ko-Btn--push ko-Btn--xl min-w-[150px]']); ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="box ko-Box mb-2">
          <div class="hd">
<?php if ($countExpired <= 0)
{
  echo 'No expired '._CJ('kanji');
}
else
{
  echo '<strong>'.$countExpired.'</strong> '.link_to('expired '._CJ('kanji'), '@review', ['class' => 'expired', 'query_string' => 'type=expired&box=all', 'title' => 'Review expired '._CJ('kanji')]);
}
?>
          </div>
          <div class="bd">
            <?= _bs_button('Review', '@overview', ['class' => 'ko-Btn ko-Btn--push ko-Btn--xl min-w-[150px]']); ?>
          </div>
        </div>
      </div>
  
      <div class="col-lg-4">
        <div class="box ko-Box mb-2">
          <div class="hd">
<?php if ($progress->heisignum === false)
{
  // kanji added out of order, display count of flashcards in RTK1-3
  $count = ReviewsPeer::getFlashcardCount($sf_user->getUserId());
  echo '<strong>'.$count.' of '.rtkIndex::inst()->getNumCharactersVol3().'</strong> flashcards';
}
      elseif ($progress->heisignum < rtkIndex::inst()->getNumCharactersVol1())
      {
        echo 'Lesson <strong>'.$progress->curlesson.' of 56</strong>'; //<br />'.$progress->kanjitogo.' '._CJ('kanji').' to go';
      }
      else
      {
        echo 'RTK 1 completed!';
      }
?>
          </div>
          <div class="bd">
            <?= _bs_button('Progress chart', '@progress', ['class' => 'ko-Btn ko-Btn--push ko-Btn--xl min-w-[150px]']); ?>
          </div>
        </div>
      </div>
  
    </div><!-- /row -->
  </div><!-- /col-lg-10 fix -->

</div>

<?php include_partial('news/recent'); ?>
