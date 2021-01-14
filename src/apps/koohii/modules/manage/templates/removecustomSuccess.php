<?php use_helper('Form', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', ['active' => 'removecustom']) ?>
          
          <h2>Remove Custom Flashcard Selection</h2>

          <div class="ajax">
            <?php include_partial('RemoveCustom') ?>
          </div>

<?php decorate_end() ?>
