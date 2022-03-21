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

<div class="mt-3 mb-8">
<?php echo form_tag('manage/removeListConfirm', ['class' => 'main-form']) ?>
<?php
    echo _bs_submit_tag('Remove Cards') . '<em class="note">Note: there will be a confirmation step.</em>';
    echo _bs_button(
      'Clear selection',
      'manage/removelist',
      ['class' => 'ko-Btn ko-Btn--danger ml-4']
    );
?>
</form>
</div>

<?php endif ?>
