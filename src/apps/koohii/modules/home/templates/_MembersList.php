<?php use_helper('SimpleDate', 'Links', 'Widgets') ?>

<?php /* Dec.2104 : show member count only to admin */ $userName = $sf_user->getUserName(); if ($userName === 'admin' || $userName === 'fuaburisu'): ?>
<p><strong><?php echo $pager->getNbResults() ?></strong> members have been reviewing in the past 30 days.</p>
<?php endif ?>

<?php echo form_tag('home/memberslisttable') ?>
  <?php echo ui_select_table($table, $pager) ?>
</form>

