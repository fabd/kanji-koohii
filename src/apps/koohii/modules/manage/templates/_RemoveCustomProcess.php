<?php use_helper('Form', 'Validation', 'Widgets') ?>
<?php echo form_tag('manage/RemoveCustomProcess', ['class' => 'main-form']) ?>

  <?php echo form_errors() ?>

<?php if (is_array($cards) && !count($cards)): ?>

  <p> No flashcards matched the selection, nothing deleted.</p>

<?php elseif (is_array($cards)): ?>

  <p> The following <strong><?php echo $count ?></strong> <?php echo _CJ('kanji') ?> flashcard(s) have been removed:</p>
  
  <div style="background:#E7F5CD;color:#000;padding:5px;margin:0 0 1em;">
<?php
  $kanjis = [];
  foreach ($cards as $id)
  {
    $kanjis[] = rtkIndex::getCharForIndex($id);
  }
  echo implode(', ', $kanjis);
?>
  </div>

<?php endif; ?>

  <p><a href="#" class="btn btn-primary JSManageReset">Remove more cards</a></p>

</form>
