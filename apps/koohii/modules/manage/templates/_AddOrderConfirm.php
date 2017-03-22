<?php use_helper('CJK', 'Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/addOrderProcess', array('class' => 'main-form')) ?>

  <?php if (!$countNewCards): ?>

  <p> All <?php echo _CJ('kanji') ?> in the selection are already present in your flashcards.</p>

  <?php else: ?>

  <p> <strong><?php echo $countNewCards ?></strong> new card(s) will be added:</p>  

<?php include_partial('CharacterSelection', array('cards' => $newCards)) ?>

  <?php endif ?>

  <p>
    <?php if ($countNewCards) { echo submit_tag('Add Cards') . '&nbsp;&nbsp;'; } ?><a href="#" class="cancel JSManageCancel">Go back</a>
  </p>


</form>

