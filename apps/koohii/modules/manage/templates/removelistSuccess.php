<?php use_helper('Form', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', array('active' => 'removelist')) ?>
          
          <h2>Remove Flashcards From List</h2>

          <div class="ajax">
            <?php include_partial('RemoveList') ?>
          </div>

<?php decorate_end() ?>
