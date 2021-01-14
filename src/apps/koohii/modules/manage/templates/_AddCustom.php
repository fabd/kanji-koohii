<?php use_helper('Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/addCustomConfirm', ['class' => 'main-form']) ?>

<div class="markdown">
  <p> To add a custom selection of flashcards, enter one or more of the following:</p>
  <ul>
    <li><?php echo _CJ('Remembering the Kanji') ?> frame numbers, eg: "1, 3, 5" or "1 3 5"<br/>
        <strong>Old Editions</strong>: for the <a href="http://nirc.nanzan-u.ac.jp/en/files/2012/12/RK1-Supplement.pdf" target="blank" class="link-pdf">RTK Supplement</a> use range "3008-3030"</li>
    <li>A range of frame numbers, eg: "10-20" or "1-5, 10-15"</li>
    <li><?php echo _CJ_U('kanji') ?> characters, eg: "一, 二, 三" or "一二三"</li>
    <li>Any other character from the "CJK Unified Ideographs" range, eg. 蜘 (spider)</li>
  </ul>
  <p> All numbers and number ranges must be separated with blanks or commas.</p>
</div>

  <?php echo form_errors() ?>
  
  <?php echo textarea_tag('txtSelection', '' /*'4 56 一　二三'*/, ['class' => 'form-control mb-1', 'rows' => 5]) ?>

  <?php echo _bs_submit_tag('Add Cards') ?><em class="note">Note: there will be a confirmation step.</em>

</form>
