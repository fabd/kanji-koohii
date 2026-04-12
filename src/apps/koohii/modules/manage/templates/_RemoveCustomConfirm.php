<?php use_helper('Form', 'Validation', 'Widgets'); ?>

<?= form_tag('manage/RemoveCustomProcess', ['class' => 'main-form']); ?>

  <?php if (!$count): ?>

  <p> No flashcards selected.</p>

  <?php else: ?>

  <p> <strong><?= $count; ?></strong> <?= _CJ('kanji'); ?> flashcard(s) <strong>will be removed</strong>:</p>  

  <?php include_partial('CharacterSelection', ['cards' => $cards]); ?>

  <?php endif; ?>

  <p>
    <?php if ($count) {
      echo _bs_submit_tag('Remove Flashcards').'&nbsp;&nbsp;';
    } ?><a href="#" class="ko-Btn is-ghost JSManageCancel">Go back</a>
  </p>


</form>

