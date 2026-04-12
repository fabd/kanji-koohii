<?php use_helper('Form', 'Validation', 'Widgets'); ?>
<?= form_tag('manage/RemoveCustomProcess', ['class' => 'main-form']); ?>

  <?= form_errors(); ?>

<?php if (is_array($cards) && !count($cards)): ?>

  <p> No flashcards matched the selection, nothing deleted.</p>

<?php elseif (is_array($cards)): ?>

  <p> The following <strong><?= $count; ?></strong> <?= _CJ('kanji'); ?> flashcard(s) have been removed:</p>
  
  <div style="background:#E7F5CD;color:#000;padding:5px;margin:0 0 1em;">
<?php
  $kanjis = [];
  foreach ($cards as $id) {
    $kanjis[] = rtkIndex::getCharForIndex($id);
  }
  echo implode(', ', $kanjis);
  ?>
  </div>

<?php endif; ?>

  <p><a href="#" class="ko-Btn is-ghost JSManageReset">Remove more cards</a></p>

</form>
