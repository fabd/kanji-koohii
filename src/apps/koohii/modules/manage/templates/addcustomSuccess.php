<?php use_helper('Form', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', array('active' => 'addcustom')) ?>
          
  <h2>Add Custom Flashcard Selection</h2>

  <div class="ajax">
    <?php include_partial('AddCustom') ?>
  </div>

<?php decorate_end() ?>
