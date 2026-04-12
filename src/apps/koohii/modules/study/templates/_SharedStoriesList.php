<?php use_helper('Form', 'CJK', 'Links', 'Widgets', 'SimpleDate'); ?>

<?php // ViewCacheLogPeer::flag('SharedStories')?>

<?php $ucsId = $sf_request->getParameter('ucsId'); ?>

<?= form_tag('study/SharedStoriesList'); ?>

  <?= input_hidden_tag('ucsId', $ucsId); ?>
  <?= input_hidden_tag('keyword', $keyword); ?>

  <?= ui_select_pager($pager); ?>

  <div id="SharedStoriesError"></div>

<?php
  foreach ($rows as $o) {
    $dispStars = $o->stars ?: '';
    $dispKicks = $o->kicks ?: '';

    $evil_story = $o->kicks >= 10 && ($o->stars / $o->kicks < 10);
    ?>
    <div class="sharedstory rtkframe <?= $evil_story ? ' is-moderated' : ''; ?>" lang="ja">

      <div class="sharedstory_author">
        <?= link_to_member($o->username); ?>
        <?php if ($evil_story): ?><a href="#" class="JsUnhide">Unhide</a><?php endif; ?>
      </div>

      <div class="ko-BookStyle">
        <div class="story"><?= $o->text; ?></div>
      </div>

      <div class="sharedstory_meta">
        <div class="lastmodified"><i class="far fa-clock"></i> <?= $o->lastmodified; ?></div>

        <div class="sharedstory-actions JsAction" id="<?= "story-{$o->userid}-{$ucsId}"; ?>" data-uid="<?= $o->userid; ?>" data-cid="<?= $ucsId; ?>" appv1="<?= $dispStars; ?>" appv2="<?= $dispKicks; ?>">
          <span class="JsMsg"></span>
<?php if ($userId != $o->userid): ?>
          <a href="#" class="sharedstory_btn JsTip JsReport" title="Report (toggle)"><i class="fa fa-fw fa-lg fa-exclamation"></i><span><?= $dispKicks; ?></span></a>
          <a href="#" class="sharedstory_btn JsTip JsCopy" title="Copy"><i class="far fa-fw fa-lg fa-copy"></i></a>
          <a href="#" class="sharedstory_btn JsTip JsStar" title="Upvote (toggle)"><i class="far fa-fw fa-lg fa-star"></i><span><?= $dispStars; ?></span></a>
<?php else: ?>
          <em class="star"><?= $dispStars; ?></em>
<?php endif; ?>
        </div>
      </div>

    </div>
<?php
  }
?>

  <?php // echo ui_select_pager($pager)?>

</form>
