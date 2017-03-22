<?php use_helper('Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/RemoveCustomProcess', array('class' => 'main-form')) ?>

  <?php if (!$count): ?>

  <p> No flashcards selected.</p>

  <?php else: ?>

  <p> <strong><?php echo $count ?></strong> <?php echo _CJ('kanji') ?> flashcard(s) <strong>will be removed</strong>:</p>  

  <?php include_partial('CharacterSelection', array('cards' => $cards)) ?>

  <?php endif ?>

  <p>
    <?php if ($count) { echo submit_tag('Remove Flashcards') . '&nbsp;&nbsp;'; } ?><a href="#" class="cancel JSManageCancel">Go back</a>
  </p>


</form>

