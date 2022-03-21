<?php
  use_helper('Widgets', 'Gadgets', 'CJK', 'Links');

  // unix timestamp recorded at start of last review to match updated flashcards
  $ts_start = $sf_params->get('ts_start', 0);

  $stats = FlashcardReview::getInstance()->getStats();
  extract($stats);

  if ($fcr_pass === $fcr_total)
  {
    $title = 'Hurrah! All remembered!';
  }
  elseif ($fcr_fail === $fcr_total && $fcr_total > 1)
  {
    $title = 'Eek! All forgotten!';
  }
  else
  {
    $title = "Remembered {$fcr_pass} of {$fcr_total} kanji.";
  }

  // deleted cards
  $deletedCards = $sf_params->get('fc_deld', '');
  $deletedCards = $deletedCards ? explode(',', $deletedCards) : [];

  //DBG::request();
?>

  <h2>Review Summary</h2>

<div class="row">
  <div class="col-lg-6 mb-8">
    <h3><?= $title; ?></h3>

    <p>Below is the list of flashcards from your last review session.
    <?php if (!$fc_free) { ?>Click the column titles to sort on frame number, keyword, etc.<?php } ?></p>
<?php if (!$fc_free) { ?>
    <p>See the <?= link_to('detailed flashcard list', 'manage/flashcardlist'); ?> for a complete list of all your flashcards and past results.</p>
<?php } ?>

<?php
    $go_back = $fc_free ? 'review/custom' : 'review/index';
    echo _bs_button('Back', $go_back, ['class' => 'ko-Btn is-ghost']);

    if ($fc_rept !== '')
    {
      echo '&nbsp;&nbsp;'._bs_button(
        '<i class="fa fa-redo mr-2"></i>Repeat Review',
        $fc_rept,
        [
          'absolute' => true,
          'class' => 'ko-Btn ko-Btn--success'
        ]
      );
    }
?>
  </div>
  
  <div class="col-lg-6">
<?php if ($fcr_total > 0) { ?>
    <div class="padded-box rounded">

      <?= ui_chart_vs([
        'valueLeft' => $fcr_pass,
        'labelLeft' => 'Remembered',
        'valueRight' => $fcr_fail,
        'labelRight' => 'Forgotten',
      ]); ?>

    </div>
<?php } ?>

<?php if (count($deletedCards)) { ?>
    <div id="FcSummaryDeld" class="padded-box rounded">
      <h3>Deleted flashcards <span>(<?= count($deletedCards); ?>)</span></h3>
      <p><?= cjk_lang_ja('&#'.implode(';&#', $deletedCards)); ?></p>
    </div>
<?php } ?>

  </div>
</div><!-- /row -->

<div style="margin-top:2em;">

<?php if ($fcr_total > 0) { ?>
  <div id="summaryTable<?= $fc_free ? ' fcfree' : ''; ?>">
    <?php
      /**
       * FIXME? Instead of using ts_start to match the last updated cards, we
       *        could use the cached answers + WHERE ucs_id IN (id1, id2, etc)
       *        to select all cards from the last review session.
       */
      if (!$fc_free && $ts_start > 0)
      {
        include_component('review', 'summaryTable', ['ts_start' => $ts_start]);
      }
      elseif ($fc_free)
      {
        include_component('review', 'summarySimple');
      }
    ?>
  </div>
<?php } ?>

</div>

<?php koohii_onload_slot(); ?>
  if (document.getElementById('summaryTable')) {
    new Koohii.UX.AjaxTable('summaryTable');
  }
<?php end_slot(); ?>
