<?php use_helper('Form', 'SimpleDate', 'CJK', 'Links', 'Widgets') ?>

<?php #echo DBG::printr(uiSelectionState::getSelection(manageActions::REMOVE_FLASHCARDS)->getState('1')) ?>
<?php #echo DBG::printr(uiSelectionState::getSelection(manageActions::REMOVE_FLASHCARDS)->getState('22')) ?>
<?php #echo DBG::printr(uiSelectionState::getSelection(manageActions::REMOVE_FLASHCARDS)->getAll()) ?>

<?php echo form_tag('manage/removeListTable') ?>
  <?php echo ui_select_table($table, $pager) ?>
</form>

