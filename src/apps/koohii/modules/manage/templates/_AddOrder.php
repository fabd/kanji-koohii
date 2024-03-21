<?php use_helper('Form', 'Links', 'Validation', 'Widgets'); ?>

<?= form_tag('manage/addOrderConfirm', ['class' => 'main-form']); ?>

<div class="markdown">
  <p> To add flashcards for <?= _CJ('RTK'); ?>:</p>
  <ul>
    <li>Enter a frame number, and all cards up to that number will be added</li>
    <li>Enter a range of cards, by using a "+" prefix, for example "+10" to add 10 flashcards.</li>
  </ul>
</div>

<?php
  echo _bs_form_group(
    ['validate' => 'txtSelection'],
    _bs_input_text('txtSelection', ['class' => 'form-control-i w-[80px] mr-4']),
    _bs_submit_tag('Add Cards')
  );
?>

  <div class="ko-Box text-body text-sm italic mb-4">
    Note! Adding flashcards here will always fill in the gaps if there are any
    missing cards.<br/>
    Use <?= link_to('Custom selection', 'manage/addcustom'); ?> if you do not plan to add all <?= _CJ('RTK'); ?> flashcards in order.
  </div>

  <div class="ko-Box">

      23 new characters from the <?= link_to_rk1_supplement() ?>
      (PDF document, ~490kb) can be added through the <?= link_to('Custom selection', 'manage/addcustom'); ?> page.
      &nbsp;See <?= link_to('update notes', '@news_by_id?id=154'/*, array('class' => 'link-article')*/); ?>.

  </div>

</form>
