<?php use_helper('Widgets', 'CJK', 'SimpleDate', 'Links'); ?>

  <h2>Flashcard List</h2>

  <div class="row mb-8">
    <div class="col-lg-6">
      <p class="ux-text-md">
      Click a column heading to sort
      the table on that column, click more than once to revert the sort order.
      Note that in addition to the column you selected, there is always a secondary
      sorting on the frame number.
      </p>
    </div>

    <div class="col-lg-6">
      <div class="ko-Box">
        <h3 class="font-bold">Statistics</h3>
        <strong><?= ReviewsPeer::getFlashcardCount($sf_user->getUserId()); ?></strong> flashcards.<br />
      </div>
    </div>

  </div>

<?php //DBG::user()?>
<?= ui_select_table($table, $pager); ?>

