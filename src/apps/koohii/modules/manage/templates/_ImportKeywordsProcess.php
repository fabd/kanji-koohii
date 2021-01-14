<?php use_helper('CJK', 'Form', 'Validation', 'Widgets') ?>

  <?php echo form_errors() ?>

<?php if (!$sf_request->hasErrors()): ?>
  <div class="confirmwhatwasdone">
    <p>Import successful.</p>
  </div>
<?php endif ?>

<?php echo form_tag('manage/importKeywords', ['class' => 'main-form']) ?>

  <p><a href="#" class="btn btn-primary JSManageCancel">Go back</a></p>

</form>
