<?php use_helper('CJK', 'Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/addOrderProcess', array('class' => 'main-form')) ?>

<?php if (!$countNewCards): ?>

  <p> All <?php echo _CJ('kanji') ?> in the selection are already present in your flashcards.</p>

<?php else: ?>

  <p> <strong><?php echo $countNewCards ?></strong> new card(s) will be added:</p>  

  <?php include_partial('CharacterSelection', array('cards' => $newCards)) ?>

<?php endif ?>

<?php if ($countNewCards) {
  echo _bs_submit_tag('Add Cards', array('style' => 'margin-right:0.5em')); }
?>
<a href="#" class="btn btn-ghost JSManageCancel">Go back</a>



</form>

