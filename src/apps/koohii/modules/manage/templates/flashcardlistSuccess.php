<?php use_helper('Widgets', 'CJK', 'SimpleDate', 'Links') ?>

  <div class="row">
    <div class="col-md-6">

      <h2>Flashcard List</h2>
      <p>
      Click a column heading to sort
      the table on that column, click more than once to revert the sort order.
      Note that in addition to the column you selected, there is always a secondary
      sorting on the frame number. Click in any row to go to the study area.<br/>
      </p>
    </div>

    <div class="col-md-6">
      <div class="padded-box rounded" style="margin:0 0 1.5em;">
        <strong>Statistics</strong><br />
        <?php echo ReviewsPeer::getFlashcardCount($sf_user->getUserId()) ?> flashcards.<br />
      </div>
    </div>

  </div>

<?php #DBG::user() ?>
<?php echo ui_select_table($table, $pager) ?>

