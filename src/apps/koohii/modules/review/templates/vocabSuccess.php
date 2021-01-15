<?php
  use_helper('Form', 'Validation', 'Links');
  $sf_request->setParameter('_homeFooter', true);
?>

<h2>Vocab Shuffle</h2>

<div class="row mb-3">
  <div class="col-md-12">

    <p><strong>A short, random flashcard session to discover new words, memorize readings, and test your kanji knowledge!</strong></p>

    <p>Each new test will display a selection from the priority entries as defined in Jim Breen's
    Japanese/English dictionary (JMDICT).</p>

    <?php #echo link_to('<span>Start Vocab Shuffle!</span>', 'labs/review', array('class' => 'uiIBtn uiIBtnDefault')) ?>

  </div><!-- /col -->
</div><!-- /row -->    

<div class="row">
  <div class="col-md-6">

    <div class="padded-box-inset labs-review-box">
    
      <h4>Discover words based on RTK index</h4>

      <p>Start a vocab session with words selected based on RTK index.</p>

      <p>Session length: up to <?php echo rtkLabs::VOCABSHUFFLE_LENGTH ?> cards (depending on existing vocabulary matching the RTK index below).</p>

<?php
      echo form_tag('labs/shuffle1', ['method' => 'post']);
      echo form_errors();
?>
      <div class="form-group">
        Use only kanji with RTK index up to
<?php
        echo input_tag('max_framenum', '20', ['style' => 'width:50px;margin:0 0.3em;', 'class' => 'form-control form-control-i'])
?>
      </div>
<?php
      echo _bs_form_group(['class' => 'mb-2'],
        _bs_submit_tag('Start Review')
      );
?>
      </form>
    
    </div><!-- /box -->

  </div><!-- /col -->       
  <div class="col-md-6">

    <div class="padded-box-inset labs-review-box">
      
      <h4>Discover words made only of learned kanji</h4>

<?php if ($learnedcount > 0): ?>
      <p><strong><?php echo $learnedcount ?></strong> learned kanji (one or more succesful reviews).</p>

      <p>Session length: up to <?php echo rtkLabs::VOCABSHUFFLE_LENGTH ?> cards (depending on existing vocabulary matching your learned kanji).</p>

<?php
      echo form_tag('labs/shuffle2', ['method' => 'post']);
      echo form_errors();

      echo _bs_form_group(['class' => 'mb-2'],
        _bs_submit_tag('Start Review')
      );
?>
      </form>
<?php else: ?>

    <p>This option will be available after <?php echo link_to('adding flashcards', '@manage') ?> and using the SRS for some time (requires kanji with three succesfull reviews).</p>

<?php endif ?>
          </div>

  </div><!-- /col -->
</div><!-- /row -->    

