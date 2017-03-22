<?php use_helper('Form', 'CJK', 'Links', 'Widgets', 'SimpleDate') ?>

<?php #ViewCacheLogPeer::flag('SharedStories') ?>

<?php $ucsId = $sf_request->getParameter('ucsId'); ?>

<?php echo form_tag('study/SharedStoriesList') ?>

  <?php echo input_hidden_tag('ucsId', $ucsId) ?>
  <?php echo input_hidden_tag('keyword', $keyword) ?>

  <?php echo ui_select_pager($pager) ?>

  <div id="SharedStoriesError"></div>

<?php
  foreach($rows as $o) {
    $evil_story = $o->kicks >= 10 && ($o->stars / $o->kicks < 10);
?>
    <div class="sharedstory rtkframe <?php echo $evil_story ? ' is-moderated':'' ?>">

      <div class="sharedstory_author">
        <?php echo link_to_member($o->username) ?>
        <?php if ($evil_story): ?><a href="#" class="JsUnhide">Unhide</a><?php endif ?>
      </div>

      <div class="bookstyle">
        <div class="story"><?php echo $o->text ?></div>
      </div>

      <div class="sharedstory_meta flex">
        <div class="lastmodified col-m flex-a-c"><i class="fa fa-clock-o"></i> <?php echo $o->lastmodified ?></div>

        <div class="actions col-m ta-r JsAction" id="<?php echo "story-{$o->userid}-${ucsId}" ?>" data-uid="<?php echo $o->userid ?>" data-cid="<?php echo $ucsId ?>" appv1="<?php echo $o->stars ?>" appv2="<?php echo $o->kicks ?>">
          <span class="JsMsg"></span>
<?php if ($userId != $o->userid): ?>
          <a href="#" class="sharedstory_btn JsTip JsReport" title="Report (toggle)"><i class="fa fa-exclamation"></i><span><?php echo $o->kicks ?></span></a>
          <a href="#" class="sharedstory_btn JsTip JsCopy" title="Copy"><i class="fa fa-copy"></i></a>
          <a href="#" class="sharedstory_btn JsTip JsStar" title="Upvote (toggle)"><i class="fa fa-star"></i><span><?php echo $o->stars ?></span></a>
<?php else: ?>
          <em class="star"><?php echo $o->stars ?></em>
<?php endif ?>
        </div>
      </div>

    </div>
<?php
  }
?>

  <?php #echo ui_select_pager($pager) ?>

</form>
