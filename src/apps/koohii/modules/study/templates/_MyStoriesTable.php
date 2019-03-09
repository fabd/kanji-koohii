<?php use_helper('Form', 'CJK', 'Links', 'Widgets', 'Date', 'SimpleDate') ?>

<?php #DBG::request() ?>

<div id="MyStoriesTableError" class="warningmessagebox" style="display:none"></div>

<div id="my-stories" class="no-gutter-xs-sm">
<?php echo form_tag('study/MyStoriesTable') ?>

  <input type="hidden" name="stories_uid" value="<?php echo (int)$stories_uid ?>"/>

  <?php echo input_hidden_tag('profile_page', (int)$profile_page) ?>

  <?php echo ui_select_pager($pager) ?>


<?php foreach($rows as $S): ?>
  <div class="sharedstory rtkframe">

        <div class="mystories-kanji"><?php echo cjk_lang_ja($S['kanji']) ?></div>

        <div class="mystories-hd">
          <div class="mystories-hd_fn td"><?php echo $S['seq_nr'] ?></div>
          <div class="mystories-hd_kw td">
            <?php echo link_to($S['keyword'], '@study_edit?id='.$S['kanji']) ?>
          </div>
        </div>

        <div class="bookstyle">
          <div class="story"><?php echo $S['story'] ?></div>
        </div>

        <div class="sharedstory_meta flex">
          <div class="lastmodified flex-a-c" title="Last edited"><i class="far fa-clock"></i> <?php echo time_ago_in_words($S['ts_dispdate']) ?> ago</div>

<?php if ($S['share']): ?>
          <div class="actions col-m ta-r">
<?php if ($S['kicks'] > 0): ?>
            <div class="sharedstory_btn"><i class="fa fa-exclamation"></i><span><?php echo $S['kicks'] ?></span></div>
<?php endif ?>
<?php if ($S['stars'] > 0): ?>
            <div class="sharedstory_btn"><i class="fa fa-star"></i><span><?php echo $S['stars'] ?></span></div>
<?php endif ?>            
          </div>
<?php else: ?>
          <div class="col-m flex-a-c ta-r">
            <div class="sharedstory_btn"><i class="fa fa-lock"></i><span>PRIVATE</span></div>
          </div>
<?php endif ?>
        </div>

  </div>
<?php endforeach ?>


  <?php echo ui_select_pager($pager) ?>

</form>
</div>