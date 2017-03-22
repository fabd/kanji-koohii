<?php use_helper('Form', 'Validation', 'Widgets') ?>

  <p> To import customized keywords each line should contain the following two fields, separated by spaces (tabs, comma):</p>
  <ul class="content">
    <li><?php echo _CJ_U('kanji') ?> <em style="color:green">or</em> index number ("frame number") <em style="color:green">or</em> <?php echo link_to('UCS', 'http://en.wikipedia.org/wiki/Universal_Character_Set') ?> code.
    <li>Custom keyword.
  </ul>

  <p> <strong>To import a list of keywords from a text file</strong>, select the contents and paste into
      the box below.
  </p>
  <p> <strong>To import from a spreadsheet</strong> make sure the first column is the character and the second 
      column the keyword. Then make a selection of the two columns and as many rows as needed, and you
      should be able to copy and paste into the box below.
  </p>

  <?php echo form_errors() ?>
  
  <?php echo form_tag('manage/importKeywords', array('class' => 'main-form')) ?>

  <?php echo textarea_tag('txtData', /*<<<EOT
4  "quattre"
二 deux
三, trois
30000 "champs de ""riz-o-lait"""
EOT*/ ''
, array('class' => 'text', 'cols' => 70, 'rows' => 5)) ?><br/>

  <?php echo submit_tag('Import Keywords') ?>&nbsp;<em class="note">Note: there will be a confirmation step.</em>

</form>
