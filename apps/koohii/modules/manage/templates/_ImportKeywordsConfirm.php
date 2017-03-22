<?php use_helper('Form', 'Widgets') ?>

<?php echo form_tag('manage/importKeywordsProcess', array('class' => 'main-form')) ?>

  <p><strong><?php echo $keywords->getCount() ?></strong> custom keyword(s) ready to import.</p>

  <p>Please review then click the confirmation button at the bottom of the list to import the keywords.</p>

  <?php echo ui_data_table($keywords) ?>

  <?php echo submit_tag('Import Keywords') ?>&nbsp;&nbsp;<a href="#" class="cancel JSManageCancel">Go back</a>

</form>
