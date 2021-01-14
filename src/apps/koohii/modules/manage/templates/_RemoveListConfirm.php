<?php use_helper('Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/removeListProcess', ['class' => 'main-form']) ?>

  <?php if (!$count): ?>

  <p> No flashcards selected.</p>

  <?php else: ?>

  <p> <strong><?php echo $count ?></strong> card(s) will be deleted:</p>  

  <?php include_partial('CharacterSelection', ['cards' => $cards]) ?>

  <?php endif ?>

  <p>
    <?php if ($count) { echo _bs_submit_tag('Delete Cards') . '&nbsp;&nbsp;'; } ?><a href="#" class="btn btn-ghost JSManageCancel">Go back</a>
  </p>


</form>

