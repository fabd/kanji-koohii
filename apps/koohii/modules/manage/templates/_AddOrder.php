<?php use_helper('Form', 'Validation', 'Widgets') ?>

<?php echo form_tag('manage/addOrderConfirm', array('class' => 'main-form')) ?>

  <?php echo form_errors() ?>
  
<div class="markdown">
  <p> To add flashcards for <?php echo _CJ('RTK') ?>:</p>
  <ul>
    <li>Enter a frame number, and all cards up to that number will be added</li>
    <li>Enter a range of cards, by using a "+" prefix, for example "+10" to add 10 flashcards.</li>
  </ul>
</div>

<?php 
  echo _bs_form_group(
    _bs_input_text('txtSelection', array('class' => 'form-control-i', 'style' => 'width:80px;margin-right:1em;')),
    _bs_submit_tag('Add Cards')
  );
?>

  <div class="padded-box-inset" style="color:#000;padding:5px 10px; font-size:11px;">
    Note: adding flashcards here will always fill in the gaps if there are any
    missing cards.<br/>
    Use <?php echo link_to('Custom selection', 'manage/addcustom') ?> if you do not plan to add all <?php echo _CJ('RTK') ?> flashcards in order.
  </div>

<?php if (!CJ_HANZI): ?>
  <div class="padded-box-inset" style="margin:1em 0 0">
    <p>
      23 new characters from the
      <a href="http://nirc.nanzan-u.ac.jp/en/files/2012/12/RK1-Supplement.pdf" target="blank" class="link-pdf">RTK Supplement</a>
      (PDF document, ~490kb) can be added through the <?php echo link_to('Custom selection', 'manage/addcustom') ?> page.
      &nbsp;See <?php echo link_to('update notes','@news_by_id?id=154'/*, array('class' => 'link-article')*/) ?>.
    </p>
  </div>
<?php endif ?>

</form>
