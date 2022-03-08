<?php use_helper('Form', 'Validation', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', ['active' => 'flashcards']) ?>

  <h2>Flashcard Settings</h2>

  <?php echo form_errors() ?>
  <?php echo form_tag('account/flashcards') ?>

    <p></p>

    <label><?php echo checkbox_tag('opt_no_shuffle', '1', 0) ?> Review <strong>new flashcards</strong> (blue pile) in RTK order.</label>
    <p class="ml-4 mt-2 mb-4">Check this option to disable shuffling the kanji from your new flashcards pile. Reviewing new cards in 
    RTK order provides additional cues that can make the first review easier, but can also help build more associations
    for memorizing.</p>

<?php /*
    ===>  We'll keep the user setting in the database for some time... until we're sure we don't need it anymore.  <===

    <label><?php echo checkbox_tag('opt_readings', '1', 0) ?> Show example On / Kun readings on the SRS flashcards.</label>
    <p class="info">An example On and Kun reading will be shown for the kanji during SRS reviews. Example words are selected
    based on their frequency of use as documented in JMDICT (roughly 16000 entries in JMDICT). For common kanji where there
    are several suitable On and/or Kun readings, the example words will be picked at random.</p>

    */ ?>

    <p>
      <?php echo submit_tag('Save changes', ['class' => 'btn btn-success']) ?>
    </p>

  </form>

<?php //DBG::printr($sf_user->getAttributeHolder()->getAll()) ?>

<?php decorate_end() ?>
