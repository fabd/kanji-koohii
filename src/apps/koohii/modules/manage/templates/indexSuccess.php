<?php use_helper('Form', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', ['active' => 'addorder']) ?>

          <h2>Add <?php echo _CJ('Remembering the Kanji') ?> flashcards</h2>

          <div class="ajax">
            <?php include_partial('AddOrder') ?>
          </div>

<?php decorate_end() ?>
