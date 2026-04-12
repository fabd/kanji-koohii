<?php use_helper('Form', 'Validation', 'Widgets'); ?>

<?= form_tag('manage/RemoveCustomConfirm', ['class' => 'main-form']); ?>

<div class="markdown">
  <p> To <span class="warning">remove a custom selection of flashcards</span>, enter one or more of the following:</p>
  <ul>
    <li><?= _CJ('Remembering the Kanji'); ?> frame numbers, eg: "1, 3, 5" or "1 3 5"</li>
    <li>A range of frame numbers, eg: "10-20" or "1-5, 10-15"</li>
    <li><?= _CJ_U('kanji'); ?> characters, separators are not required,<br/>
        eg: "一, 二, 三" or "一二三"</li>
  </ul>
  <p> All numbers and number ranges must be separated with blanks or commas.</p>
</div>

  <?= form_errors(); ?>
  
  <?= textarea_tag('txtSelection', '' /* '4 56 一　二三' */, ['class' => 'form-control mb-3', 'rows' => 5]); ?>
  <?= _bs_submit_tag('Remove Flashcards'); ?><em class="note">Note: there will be a confirmation step.</em>

</form>
