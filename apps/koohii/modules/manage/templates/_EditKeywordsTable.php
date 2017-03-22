<?php use_helper('Form', 'SimpleDate', 'CJK', 'Links', 'Widgets') ?>

<?php echo form_tag('manage/EditKeywordsTable') ?>

  <?php echo ui_select_table($table, $pager) ?>

</form>
