<?php use_helper('Form', 'Widgets', 'Decorator') ?>

<?php decorate_start('SideTabs', ['active' => 'editkeywords']) ?>
          
          <h2>Edit <?php echo _CJ_U('kanji') ?> Keywords</h2>

          <p> Note: this list lets you edit keywords from your flashcards set. To edit keywords
              for other characters, click the keyword on the <?php echo link_to('Study', 'study/index') ?> pages.
                
                </p>

          <?php if (!ReviewsPeer::getFlashcardCount($sf_user->getUserId())): ?>

            <div class="confirmwhatwasdone"><p>
              There aren't any flashcards to edit.<br/>
              <br/>
              Note: you can also edit keywords on the Study page.
            </p></div>

          <?php else: ?>

            <div id="EditKeywordsTableComponent" data-uri="<?php echo $tplEditKeywordUri ?>">
              <?php include_component('manage', 'EditKeywordsTable') ?>
            </div>

          <?php endif ?>

<?php decorate_end() ?>
