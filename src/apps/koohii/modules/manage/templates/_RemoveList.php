<?php use_helper('Form', 'Validation', 'Widgets') ?>

<?php if (!ReviewsPeer::getFlashcardCount($sf_user->getUserId())): ?>

  <p> There aren't any flashcards to delete.</p>

<?php else: ?>

  <p> <span class="warning">Remove flashcards</span> by selecting items in the list below.</p>
  <p> Removing flashcards does <em>not</em> affect stories entered on the Study page.</p>

  <?php echo form_errors() ?>
  
  <div class="selection-table">
    <?php include_component('manage', 'RemoveListTable') ?>
  </div>

<div class="mt-1 mb-2">
<?php echo form_tag('manage/removeListConfirm', array('class' => 'main-form')) ?>
<?php
    echo _bs_submit_tag('Remove Cards') . '<em class="note">Note: there will be a confirmation step.</em>';
    echo link_to('Clear selection', 'manage/removelist', array('class' => 'btn btn-danger', 'style' => 'margin-left:2em'));
?>
</form>
</div>

<?php endif ?>
