<?php use_helper('CJK', 'Form', 'Validation') ?>

<div id="edit-keyword" class="body">

  <?php echo form_errors() ?>

  <?php echo form_tag('study/editkeyword?id='.$ucs_id) ?>

    <p>Press <kbd>Enter</kbd> to save and close the dialog.</p>
    
<?php if ($sf_request->hasParameter('manage')): ?>    
    <p> Tip: press <kbd>TAB</kbd> to save and edit the next keyword.</p>
<?php endif ?>

    <!--?php echo rtkImportKeywords::MAX_KEYWORD ?> characters maximum. -->
    
    <?php echo input_tag('keyword', $keyword, array('class' => 'txt-ckw JSDialogFocus', 'autocomplete' => 'off')) ?>

    <div class="ft-right">
<?php /*if ($sf_request->hasParameter('manage')): ?>    
      <div class="ft-left">
        <?php echo submit_tag('Save & Next', array('name' => 'doNext')) ?>
      </div>
<?php endif*/ ?>
      <a href="#" class="reset" data-reset="<?php echo $orig_keyword ?>">Reset</a><?php echo submit_tag('Save') ?>
    </div>

  </form>
</div>
