<?php
use_helper('Links');
$userId = $sf_user->getUserId();
$ucsId  = $kanjiData->ucs_id;
?>
  <div id="sharedstories-new">
    <div class="sharedstories_title title JsNewest">
      <i class="fa fa-chevron-down" style="position:absolute;right:0;top:0;width:33px;height:33px;line-height:33px;"></i>
      New &amp; updated stories
    </div>
<?php
  $stories = StoriesPeer::getSharedStories((int) $kanjiData->ucs_id, $kanjiData->keyword, $sf_user->getUserId(), 'newest');
foreach ($stories as $o) {
  ?>
    <div class="sharedstory rtkframe" lang="ja">

      <div class="sharedstory_author">
        <?= link_to_member($o->username); ?>
      </div>

      <div class="ko-BookStyle">
        <div class="story"><?= $o->text; ?></div>
      </div>

      <div class="sharedstory_meta">
        <div class="lastmodified"><i class="far fa-clock"></i> <?= $o->lastmodified; ?></div>

        <div class="sharedstory-actions JsAction" id="story-<?= $o->authorid; ?>-<?= $ucsId; ?>" data-uid="<?= $o->authorid; ?>" data-cid="<?= $ucsId; ?>" appv1="<?= $o->stars; ?>" appv2="<?= $o->kicks; ?>">
          <span class="JsMsg"></span>

          <a href="#" class="sharedstory_btn JsTip JsReport"><i class="fa fa-fw fa-lg fa-exclamation"></i><span><?= $o->kicks; ?></span></a>
          <a href="#" class="sharedstory_btn JsTip JsCopy"><i class="far fa-fw fa-lg fa-copy"></i></a>
          <a href="#" class="sharedstory_btn JsTip JsStar"><i class="far fa-fw fa-lg fa-star"></i><span><?= $o->stars; ?></span></a>



        </div>
      </div>

    </div>
<?php
}
?>
  </div>

  <div id="sharedstories-fav">
    <div class="sharedstories_title title">
      Shared stories
    </div>

    <?php // See /web/revtk/components/SharedStoriesComponent.js?>
    <div id="SharedStoriesListComponent">
      <?php include_component('study', 'SharedStoriesList'); ?>
    </div>
  </div>

