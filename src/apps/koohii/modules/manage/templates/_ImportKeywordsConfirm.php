<?php use_helper('Form', 'Widgets') ?>

<?php echo form_tag('manage/importKeywordsProcess', ['class' => 'main-form']) ?>

  <p><strong><?php echo $keywords->getCount() ?></strong> custom keyword(s) ready to import.</p>

  <p>Please review then click the confirmation button at the bottom of the list to import the keywords.</p>

  <?php echo ui_data_table($keywords) ?>

  <?php echo _bs_submit_tag('Import') ?>&nbsp;&nbsp;<a href="#" class="btn btn-ghost JSManageCancel">Go back</a>

</form>
