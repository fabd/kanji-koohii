<?php
  use_helper('Form', 'Validation', 'Links');
  $sf_request->setParameter('_homeFooter', true);
?>

<h2>Vocab Shuffle</h2>

<div class="text-smx mb-6">
  <p><strong>A short, random flashcard session to discover new words, memorize readings, and test your kanji knowledge!</strong></p>

  <p>Each new test will display a selection from the <?php echo link_to('priority entries', '@learnmore#dictionary-sources') ?> as defined in Jim Breen's
  Japanese/English dictionary (JMDICT).</p>
</div>

<div class="row">
  <div class="col-lg-6">

    <div class="ko-Box ko-Box--customReview mb-4">
    
      <h3 class="ko-Box-title">Discover words based on RTK index</h3>

      <p>Start a vocab session with words selected based on RTK index.</p>

      <p>Session length: up to <?php echo rtkLabs::VOCABSHUFFLE_LENGTH ?> cards (depending on existing vocabulary matching the RTK index below).</p>

<?php
      echo form_tag('labs/shuffle1', ['method' => 'post']);
      echo form_errors();
?>
      <div class="form-group">
        Use only kanji with RTK index up to
<?php
        echo input_tag('max_framenum', '20', ['class' => 'form-control form-control-i w-[60px] mx-2'])
?>
      </div>
<?php
      echo _bs_form_group(['class' => 'mb-0'],
        _bs_button(
          'Start Review<i class="fa fa-arrow-right ml-2"></i>',
          ['class' => 'ko-Btn ko-Btn--success ko-Btn--large']
        )
      );
?>
      </form>
    
    </div><!-- /box -->

  </div><!-- /col -->       
  <div class="col-lg-6">

    <div class="ko-Box ko-Box--customReview mb-4">
      
      <h3 class="ko-Box-title">Discover words made only of learned kanji</h3>

<?php if ($learnedcount > 0): ?>
      <p><strong><?php echo $learnedcount ?></strong> learned kanji (one or more succesful reviews).</p>

      <p>Session length: up to <?php echo rtkLabs::VOCABSHUFFLE_LENGTH ?> cards (depending on existing vocabulary matching your learned kanji).</p>

<?php
      echo form_tag('labs/shuffle2', ['method' => 'post']);
      echo form_errors();

      echo _bs_form_group(['class' => 'mb-0'],
        _bs_button(
          'Start Review<i class="fa fa-arrow-right ml-2"></i>',
          ['class' => 'ko-Btn ko-Btn--success ko-Btn--large']
        )
      );
?>
      </form>
<?php else: ?>

    <p>This option will be available after <?php echo link_to('adding flashcards', '@manage') ?> and using the SRS for some time (requires kanji with three succesfull reviews).</p>

<?php endif ?>
          </div>

  </div><!-- /col -->
</div><!-- /row -->    

