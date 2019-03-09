<?php use_helper('CJK', 'Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/addCustomProcess', array('class' => 'main-form')) ?>

  <?php if ($countNewCards==0 && $countExistCards): ?>

  <p> All <?php echo _CJ('kanji') ?> in the selection are already present in your flashcards.</p>

  <?php else: ?>

  <p> <strong><?php echo $countNewCards ?></strong> new card(s) will be added<?php if ($countExistCards) { echo sprintf(
      ' (%d are already in your flashcards)', $countExistCards); } ?>:</p>  

<?php include_partial('CharacterSelection', array('cards' => $newCards)) ?>

  <?php endif ?>

  <p>
    <?php if ($countNewCards > 0) { echo _bs_submit_tag('Add Cards') . '&nbsp;&nbsp;'; } ?><a href="#" class="btn btn-ghost JSManageCancel">Go back</a>
  </p>


</form>

