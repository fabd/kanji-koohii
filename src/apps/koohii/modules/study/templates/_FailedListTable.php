<?php use_helper('Form', 'CJK', 'SimpleDate', 'Links', 'Widgets') ?>

<?php echo form_tag('study/failedlisttable') ?>
  <?php echo ui_select_table($table, $pager) ?>
</form>

<?php koohii_onload_slot(); ?>
  new Koohii.UX.AjaxTable('FailedListTable');
<?php end_slot(); ?>
