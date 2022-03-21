<?php use_helper('Form', 'Validation', 'Widgets') ?>
<?php echo form_tag('manage/removeListProcess', ['class' => 'main-form']) ?>

  <?php echo form_errors() ?>

<?php if (is_array($cards) && !count($cards)): ?>

  <p> No flashcards matched the selection, nothing deleted.</p>

<?php elseif (is_array($cards)): ?>

  <p> The following <strong><?php echo $count ?></strong> <?php echo _CJ('kanji') ?> have been removed from your flashcards:</p>
  
  <div style="background:#E7F5CD;color:#000;padding:5px;margin:0 0 1em;font-size:24px;">
<?php
  use_helper('CJK');
  $kanjis = [];
  foreach ($cards as $id)
  {
    $kanjis[] = rtkIndex::getCharForIndex($id);
  }
  echo cjk_lang_ja( implode(', ', $kanjis) );
?>
  </div>

<?php endif; ?>

  <p><a href="#" class="ko-Btn is-ghost JSManageReset">Delete more cards</a></p>

</form>
