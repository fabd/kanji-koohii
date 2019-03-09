<?php use_helper('Widgets', 'SimpleDate', 'Form', 'CJK', 'Links') ?>

<?php #DBG::request() ?>

<?php echo form_tag('review/summaryTable') ?>
  <?php echo input_hidden_tag('ts_start', 0) ?>

<?php echo ui_select_table($table, $pager) ?>

</form>