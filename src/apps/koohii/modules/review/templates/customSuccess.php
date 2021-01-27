<?php
  use_helper('Form', 'Validation', 'Links');
  $sf_request->setParameter('_homeFooter', true);
?>

<h2>Kanji Review</h2>
      
<div class="row">
  <div class="col-md-12">

    <div class="section">
      <div class="p-notice">
        <i class="far fa-question-circle"></i><strong>Review modes below do <u>not</u> use Spaced Repetition (SRS).</strong>
      </div>

      <p class="mb-2">
        Your progress here will not be stored, but you can <strong>repeat</strong> those reviews as many times as you want.
      </p>

      <p class="mb-2">
        To save your results and schedule reviews, <?php echo link_to('add flashcards', '@manage') ?> and then use the SRS page.
      </p>
    </div>

  </div><!-- /col -->
</div><!-- /row -->
<div class="row">
  <div class="col-md-6">

    <div class="padded-box rounded labs-review-box">

      <?php echo form_tag('review/free', ['method' => 'get']) ?>
      
      <h4>Review a range of kanji</h4>

      <div class="form-group">
        RTK Index
        <?php echo input_tag('from', 1, ['style' => 'width:4.5em;margin:0 0.3em;', 'class' => 'form-control form-control-i']) ?>
        to
        <?php echo input_tag('to', 10, ['style' => 'width:4.5em;margin:0 0.3em;', 'class' => 'form-control form-control-i']) ?>
      </div>

      <div class="form-group">
      <?php $options_lessons = array_merge([0 => '---'], rtkIndex::getLessonsDropdown()) ?>
        RTK Lesson<?= select_tag('lesson', options_for_select($options_lessons, $sf_request->getParameter('lesson')), ['class' => 'form-control form-control-i', 'style' => 'width:14em;margin:0 0.3em;']); ?>
      </div>

<?php 
      echo _bs_form_group(
        _bs_input_checkbox('shuffle', ['label' => 'Shuffle cards'])
      );
      echo _bs_form_group(
        _bs_input_checkbox('reverse', ['label' => 'Kanji to Keyword (reverse mode)'])
      );
      echo _bs_form_group(
        ['class' => 'mb-2'],
        _bs_submit_tag('Start Review')
      );
?>
      </form>

    </div>
        
  </div><!-- /col -->
  <div class="col-md-6">

    <div class="padded-box rounded labs-review-box">

      <h4>Review from learned kanji</h4>

      <p>You have <strong><?php echo $knowncount ?></strong> learned kanji (<strong class="clr-srs-due">due</strong> and <strong class="clr-srs-undue">scheduled</strong> cards).</p>

<?php if ($knowncount > 0): ?>
      <?php echo form_tag('review/free', ['method' => 'get']) ?>       
<?php 
      echo _bs_form_group(
        _bs_input_checkbox('reverse', ['label' => 'Kanji to Keyword (reverse mode)'])
      );
?>
      <div class="form-group">
        Review
        <?php echo input_tag('known', $knowndefault, ['style' => 'width:4.5em;margin:0 0.3em;', 'class' => 'form-control form-control-i']) ?>
        of <?php echo $knowncount ?> learned kanji.
      </div>
<?php
      echo _bs_form_group(
        ['class' => 'mb-2'],
        _bs_submit_tag('Start Review')
      );
?>
      </form>
<?php else: ?>

      <p><i class="fa fa-info-circle"></i> This mode is available once you <strong>add flashcards</strong> to the SRS
        and have at least one succesful review.
      </p>

<?php endif ?>

    </div>

  </div><!-- /col -->
</div><!-- /row -->
