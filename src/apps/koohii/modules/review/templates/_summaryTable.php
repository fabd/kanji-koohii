<?php use_helper('Widgets', 'SimpleDate', 'Form', 'CJK', 'Links'); ?>

<?php //DBG::request()?>

<?= form_tag('review/summaryTable'); ?>
  <?= input_hidden_tag('ts_start', 0); ?>

<?= ui_select_table($table, $pager); ?>

</form>