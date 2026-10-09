<?php use_helper('CJK', 'Form', 'Validation', 'Widgets'); ?>

  <?= form_errors(); ?>

<?php if (!$sf_request->hasErrors()): ?>
  <div class="ko-Box ko-Box--success my-4">
    <p>Import successful.</p>
  </div>
<?php endif; ?>

<?= form_tag('manage/importKeywords', ['class' => 'main-form']); ?>

  <p><a href="#" class="ko-Btn is-ghost JSManageCancel">Go back</a></p>

</form>
