<?php use_helper('Form', 'CJK', 'Links', 'Widgets', 'Date', 'SimpleDate'); ?>

<?php // DBG::request()?>

<div id="MyStoriesTableError" class="warningmessagebox" style="display:none"></div>

<div id="my-stories" class="no-gutter-xs-sm">
<?= form_tag('study/MyStoriesTable'); ?>

  <?= input_hidden_tag('stories_uid', (int) $stories_uid); ?>
  <?= input_hidden_tag('profile_page', (int) $profile_page); ?>

  <?= ui_select_pager($pager); ?>


<?php foreach ($rows as $S): ?>
  <div class="sharedstory rtkframe">

        <div class="mystories-kanji"><?= cjk_lang_ja($S['kanji']); ?></div>

        <div class="mystories-hd">
          <div class="mystories-hd_fn td"><?= $S['seq_nr']; ?></div>
          <div class="mystories-hd_kw td">
            <?= link_to($S['keyword'], '@study_edit?id='.$S['kanji']); ?>
          </div>
        </div>

        <div class="ko-BookStyle">
          <div class="story"><?= $S['story']; ?></div>
        </div>

        <div class="sharedstory_meta">
          <div class="lastmodified" title="Last edited"><i class="far fa-clock"></i> <?= time_ago_in_words($S['ts_dispdate']); ?> ago</div>

<?php if ($S['share']): ?>
          <div class="sharedstory-actions">
<?php if ($S['kicks'] > 0): ?>
            <div class="sharedstory_btn"><i class="fa fa-exclamation"></i><span><?= $S['kicks']; ?></span></div>
<?php endif; ?>
<?php if ($S['stars'] > 0): ?>
            <div class="sharedstory_btn"><i class="fa fa-star"></i><span><?= $S['stars']; ?></span></div>
<?php endif; ?>            
          </div>
<?php else: ?>
          <div class="sharedstory-actions">
            <div class="sharedstory_btn"><i class="fa fa-lock"></i><span>PRIVATE</span></div>
          </div>
<?php endif; ?>
        </div>

  </div>
<?php endforeach; ?>


  <?= ui_select_pager($pager); ?>

</form>
</div>