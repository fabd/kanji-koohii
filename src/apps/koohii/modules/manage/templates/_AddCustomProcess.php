<?php use_helper('CJK', 'Form', 'Validation', 'Widgets'); ?>
<?= form_tag('manage/addCustomProcess', ['class' => 'main-form']); ?>

  <?= form_errors(); ?>

  <p> The following <strong><?= $count; ?></strong> <?= _CJ('kanji'); ?> have been added to your flashcards:</p>
  
  <div style="background:#E7F5CD;color:#000;padding:5px;margin:0 0 1em;">
<?php
  $kanjis = [];
foreach ($cards as $id) {
  $kanjis[] = rtkIndex::getCharForIndex($id);
}
echo implode(', ', $kanjis);
?>
  </div>

  <p><a href="#" class="ko-Btn is-ghost JSManageReset">Add more cards</a></p>

</form>
