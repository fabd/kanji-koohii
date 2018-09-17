<?php
  use_helper('CJK');

  $userId = $sf_user->getUserId();
  $ucsId  = $kanjiData->ucs_id;

  // prepare story component ( do it HERE because we use it in the vue placeholder + JS below)
  $formatKeyword  = $custKeyword ?: $kanjiData->keyword;
  $savedStory     = StoriesPeer::getStory($userId, $ucsId);
  $savedStoryText = $savedStory ? $savedStory->text : '';
  $formattedStory = StoriesPeer::getFormattedStory($savedStoryText, $formatKeyword, true);

  // IF ... on Study page, is in the red pile, is not yet "Added to learn list"
  $isRestudyKanji   = ReviewsPeer::isFailedCard($userId, $ucsId);
  $isRelearnedKanji = LearnedKanjiPeer::hasKanji($userId, $ucsId);
  $showLearnButton    = /*!$reviewMode &&*/ $isRestudyKanji && !$isRelearnedKanji;
  $showLearnedMessage = /*!$reviewMode &&*/ $isRestudyKanji && $isRelearnedKanji;



function get_flashcard_button($userId, $context, $ucsId) {
  $has_flashcard = intval(ReviewsPeer::hasFlashcard($userId, $ucsId));
  $dialogUri  = $context->getController()->genUrl('flashcards/dialog');
  $params     = esc_specialchars(coreJson::encode(array('ucs' => intval($ucsId))));
//<div id="EditFlashcard" class="f$bFlashcard">
//  <a href="#" title="Edit Flashcard" class="uiGUI JsEditFlashcard" data-uri="$dialogUri" data-param="$params">&nbsp;</a>
//</div>

  return <<<EOD
<div id="EditFlashcard" class="is-toggle-$has_flashcard">
  <a href="#" class="uiGUI btn btn-success JsEditFlashcard is-0" title="Add  Card" data-uri="$dialogUri" data-param="$params"
><i class="fa fa-plus"></i>Add Card</a>
  <a href="#" class="uiGUI btn btn-ghost JsEditFlashcard is-1" title="Edit Card" data-uri="$dialogUri" data-param="$params"
><i class="fa fa-edit"></i>Edit Card</a>
</div>
EOD;
}
?>

<div class="row">

<?php include_partial('SideColumn', array('kanjiData' => $kanjiData, 'intro' => false )) ?>

  <div class="col-md-9">

<?php if (!$kanjiData): ?>
  
  <div class="app-header">
    <h2>Search : No results</h2>
    <div class="clear"></div>
  </div>
  
  <?php $oRTK = rtkIndex::inst() ?>

  <p> Sorry, there are no results for "<strong><?php echo esc_specialchars($sf_params->get('id')) ?></strong>".</p>

  <p> Valid frame numbers for <strong><?php echo $oRTK->getSequenceName() ?></strong> are #1 to #<?php echo $oRTK->getNumCharacters() ?>.</p>

  <p> To search for characters outside of the selected index, type in a character or a unicode value.</p>

<?php else: ?>     

  <div id="EditStoryComponent">
    
    <div style="position:relative;">
      <h2><?php echo $title; ?></h2>
      <?php if (CJK::isCJKUnifiedUCS($kanjiData->ucs_id)) { echo get_flashcard_button($userId, $sf_context, $kanjiData->ucs_id); } ?>
    </div>

    <div id="JsEditStoryInst" style="min-height:100px;">     

<!-- placeholder till Vue comp is mounted -->
<?php
  include_partial('EditStoryPlaceholder', [
    'kanjiData' => $kanjiData, 'formattedStory' => $formattedStory, 'custKeyword' => $custKeyword]) ?>
   
    </div>

  </div>

  <?php if (!CJ_HANZI): ?>
  <div id="DictStudy" class="col-box no-gutter-xs-sm">
    <div id="DictHead" class="JsToggle">
      <i class="fa fa-chevron-down" style="position:absolute;right:0;top:0;width:33px;height:33px;line-height:33px;"></i>
      Dictionary
    </div>
    <div id="DictBody" style="display:none">
      <div id="DictPanel" data-uri="<?php echo $sf_context->getController()->genUrl('study/dict') ?>" data-ucs="<?php echo $kanjiData->ucs_id ?>">
        <div class="JsDictLoading">Loading...</p></div>
      </div>
    </div>
  </div>
  <?php endif ?>


  <div id="SharedStoriesComponent" class="col-box no-gutter-xs-sm">

    <div id="sharedstories-top">
      <div class="sharedstories_title title">
        Favourite(s)
      </div>
<?php
  // req. ucsId, userId
  use_helper('Links');
  $stories = StoriesPeer::getSharedStories((int)$kanjiData->ucs_id, $kanjiData->keyword, $userId, 'starred');
  foreach($stories as $o) {
?>
      <div class="sharedstory rtkframe">
        
        <div class="sharedstory_author">
          <?php echo link_to_member($o->username) ?>
        </div>

        <div class="bookstyle">
          <div class="story"><?php echo $o->text ?></div>
        </div>

        <div class="sharedstory_meta flex">
          <div class="lastmodified col-m-1 flex-a-c"><i class="far fa-clock"></i> <?php echo $o->lastmodified ?></div>

          <div class="actions col-m ta-r JsAction" data-uid="<?php echo $o->authorid ?>" data-cid="<?php echo $ucsId ?>" appv1="<?php echo $o->stars ?>" appv2="<?php echo $o->kicks ?>">
            <span class="JsMsg"></span>
            <a href="#" class="sharedstory_btn JsTip JsCopy"><i class="far fa-fw fa-lg fa-copy"></i></a>
<?php if ($userId != $o->authorid): ?>
            <a href="#" class="sharedstory_btn JsTip JsStar"><i class="far fa-fw fa-lg fa-star"></i><span><?php echo $o->stars ?></span></a>
<?php else: ?>
            <em class="star"><?php echo $o->stars ?></em>
<?php endif ?>
          </div>
        </div>
      </div>

<?php } ?>
    </div>

    <?php
    // Caching of this partial is dynamically enabled in study/edit action. Use sf_cache_key for removals.
    include_partial('SharedStories', array('sf_cache_key' => $kanjiData->ucs_id, 'kanjiData' => $kanjiData));
    ?>
  </div>


<?php #ViewCacheLogPeer::log('SharedStories', $kanjiData->framenum); ?>

<?php endif ?>

  </div><!-- /col -->

</div><!-- /row -->

<?php

    //FIXME
    // // Learned button for Study page only
    // if (!$request->hasParameter('reviewMode'))
    // {
    //   $this->isRestudyKanji = ReviewsPeer::isFailedCard($userId, $ucsId);
    //   $this->isRelearnedKanji = LearnedKanjiPeer::hasKanji($userId, $ucsId);
    // }

  $propsData = [
    'kanjiData'     => $kanjiData,
    'custKeyword'   => $custKeyword,

    'initStory_Edit'   => $savedStoryText,
    'initStory_View'   => $formattedStory,
    'initStory_Public' => (bool) ($savedStory && $savedStory->public),

    // Study page only (not for flashcards "edit story" dialog)
    'showLearnButton'    => $showLearnButton,
    'showLearnedMessage' => $showLearnedMessage
  ];

  koohii_onload_slot();
?>

  Koohii.Refs.vueEditStory = mountEditStoryComponent('#JsEditStoryInst', <?= json_encode($propsData) ?>);

<?php end_slot() ?>
