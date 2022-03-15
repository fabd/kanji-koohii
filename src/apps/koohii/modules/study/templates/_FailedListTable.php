<?php use_helper('Form', 'CJK', 'SimpleDate', 'Links', 'Widgets'); ?>

<div id="FailedListTable">
  <?= form_tag('study/failedlisttable'); ?>
    <?= ui_select_table($table, $pager); ?>
  </form>
</div>

<?php koohii_onload_slot(); ?>
  new Koohii.UX.AjaxTable('FailedListTable');
<?php end_slot(); ?>
