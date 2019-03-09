<?php use_helper('Form', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', array('active' => 'exportflashcards')) ?>
          
          <h2>Export your kanji flashcards</h2>
          
          <p>Click the link below to download your flashcards and current review status. The data
             is exported as a CSV file, using UTF-8 encoding.
             

          <p><?php echo ui_ibtn('Export flashcards', 'manage/exportflashcards', array('icon' => 'export')) ?></p>
          
<?php decorate_end() ?>
