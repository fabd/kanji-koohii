<?php use_helper('Form', 'Validation', 'Decorator'); ?>

<?php decorate_start('SideTabs', ['active' => 'spacedrepetition']); ?>

  <h2>Spaced Repetition Settings</h2>

  <p>
    Learn more about Koohii’s <?= link_to('Leitner SRS implementation', '@learnmore#srs'); ?> in the documentation.
  </p>

  <p>
    Also see the <b>tips</b> in the <?= link_to('Card Ratings', '@learnmore#rating'); ?> section.
  </p>

  <div class="pb-4"></div>

  <?= form_errors(); ?>
  <?= form_tag('account/spacedrepetition'); ?>
    <div id="JsVueMountSrsForm"></div>
    <p>
      <?= submit_tag('Save changes', ['class' => 'ko-Btn ko-Btn--success']); ?>
    </p>
  </form>

<?php decorate_end(); ?>

<?php
  kk_globals_put('ACCOUNT_SRS', $srsSettings);
