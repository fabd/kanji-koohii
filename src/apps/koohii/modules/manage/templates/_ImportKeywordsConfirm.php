<?php use_helper('Form', 'Widgets'); ?>

<?= form_tag('manage/importKeywordsProcess', ['class' => 'main-form']); ?>

  <p><strong><?= $keywords->getCount(); ?></strong> custom keyword(s) ready to import.</p>

  <p>Please review then click the confirmation button at the bottom of the list to import the keywords.</p>

  <?= ui_data_table($keywords); ?>

  <?= _bs_submit_tag('Import'); ?>&nbsp;&nbsp;<a href="#" class="ko-Btn is-ghost JSManageCancel">Go back</a>

</form>
