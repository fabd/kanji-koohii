<?php use_helper('Form', 'Validation', 'Decorator'); ?>

<?php decorate_start('SideTabs', ['active' => 'spacedrepetition']) ?>

  <h2>Spaced Repetition Settings</h2>

  <p>
    See <?= link_to('documentation', '@learnmore#srs') ?> for Kanji Koohii's implementation of the Leitner SRS.
  </p
  <p>
    Also see <?= link_to('tips for rating cards', '@learnmore#faq') ?> in the F.A.Q.
  </p>

  <?php echo form_errors() ?>
  <?php echo form_tag('account/spacedrepetition') ?>
    <div id="JsVueMountSrsForm"></div>
    <p>
      <?php echo submit_tag('Save changes', ['class' => 'btn btn-success']) ?>
    </p>
  </form>

<?php decorate_end() ?>

<?php
  kk_globals_put('ACCOUNT_SRS', $srsSettings);
