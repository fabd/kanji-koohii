<?php use_helper('Form', 'SimpleDate', 'CJK', 'Links', 'Widgets'); ?>

<?= form_tag('manage/EditKeywordsTable'); ?>

  <?= ui_select_table($table, $pager); ?>

</form>
