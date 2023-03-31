<?php
  use_helper('Widgets', 'Form', 'Gadgets', 'CJK', 'Links');

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

  // handle the repeat button for Custom Review modes
  $repeat_button_html = '';
  if ($fc_rept) {
    $formData = json_decode($fc_rept, true) ?? [];
    $formAction = $formData['action'];
    unset($formData['action']);

    $repeat_button_html = form_with_data(
      $formAction,
      $formData,
      ['class' => 'inline-block ml-4'],
      _bs_button(
        '<i class="fa fa-redo mr-2"></i>Repeat Review',
        [ 'class' => 'ko-Btn ko-Btn--success' ]
      )
    );
  }
?>

  <h2>Review Summary</h2>

<div class="row">
  <div class="col-lg-6 mb-8">
    <h3 class="font-bold"><?= $title; ?></h3>

    <p>Below is the list of flashcards from your last review session.
    <?php if (!$fc_free) { ?>Click the column titles to sort on frame number, keyword, etc.<?php } ?></p>
<?php if (!$fc_free) { ?>
    <p>See the <?= link_to('detailed flashcard list', 'manage/flashcardlist'); ?> for a complete list of all your flashcards and past results.</p>
<?php } ?>

<?php
    $go_back = $fc_free ? 'review/custom' : 'review/index';
    echo _bs_button_to('Back', $go_back, ['class' => 'ko-Btn is-ghost']);
    
    echo $repeat_button_html;
?>
  </div>
  
  <div class="col-lg-6">
<?php if ($fcr_total > 0) { ?>
    <div class="ko-Box">

      <?= ui_chart_vs([
        'valueLeft' => $fcr_pass,
        'labelLeft' => 'Remembered',
        'valueRight' => $fcr_fail,
        'labelRight' => 'Forgotten',
      ]); ?>

    </div>
<?php } ?>

<?php if (count($deletedCards)) { ?>
    <div id="FcSummaryDeld" class="ko-Box">
      <h3>Deleted flashcards <span>(<?= count($deletedCards); ?>)</span></h3>
      <p><?= cjk_lang_ja('&#'.implode(';&#', $deletedCards)); ?></p>
    </div>
<?php } ?>

  </div>
</div><!-- /row -->

<div style="margin-top:2em;">

<?php if ($fcr_total > 0) { ?>
  <div id="KoReviewSummaryTable">
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
  const el = document.getElementById('KoReviewSummaryTable');
  el && new Koohii.UX.AjaxTable(el);
<?php end_slot(); ?>
