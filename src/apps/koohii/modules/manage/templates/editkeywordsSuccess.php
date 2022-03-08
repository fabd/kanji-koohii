<?php use_helper('Form', 'Widgets', 'Decorator'); ?>

<?php decorate_start('SideTabs', ['active' => 'editkeywords']); ?>
          
<h2>Edit <?= _CJ_U('kanji'); ?> Keywords</h2>

<p>Here you can edit keywords from your flashcards set.</p>
  
<p class="text-sm italic">
To edit keywords for kanji not currently in your flashcards, click the keyword on the <?= link_to('Study', 'study/index'); ?> pages.
</p>

<?php if (!ReviewsPeer::getFlashcardCount($sf_user->getUserId())): ?>

<div class="confirmwhatwasdone">
  <p>
  There aren't any flashcards to edit.<br/>
  <br/>
  Note: you can also edit keywords on the Study page.
  </p>
</div>

<?php else: ?>

  <div id="EditKeywordsTableComponent" data-uri="<?= $tplEditKeywordUri; ?>">
    <?php include_component('manage', 'EditKeywordsTable'); ?>
  </div>

<?php endif; ?>

<?php decorate_end(); ?>
