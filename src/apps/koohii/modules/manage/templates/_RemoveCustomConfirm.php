<?php use_helper('Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/RemoveCustomProcess', ['class' => 'main-form']) ?>

  <?php if (!$count): ?>

  <p> No flashcards selected.</p>

  <?php else: ?>

  <p> <strong><?php echo $count ?></strong> <?php echo _CJ('kanji') ?> flashcard(s) <strong>will be removed</strong>:</p>  

  <?php include_partial('CharacterSelection', ['cards' => $cards]) ?>

  <?php endif ?>

  <p>
    <?php if ($count) { echo _bs_submit_tag('Remove Flashcards') . '&nbsp;&nbsp;'; } ?><a href="#" class="btn btn-ghost JSManageCancel">Go back</a>
  </p>


</form>

