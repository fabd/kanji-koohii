<?php use_helper('Form', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', array('active' => 'importkeywords')) ?>

          <h2>Import Customized Keywords</h2>

          <div class="ajax">
            <?php include_partial('ImportKeywords') ?>
          </div>

<?php decorate_end() ?>
