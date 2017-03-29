<?php use_helper('CJK', 'Form', 'Validation') ?>


<?php /*
$db = sfProjectConfiguration::getActive()->getDatabase();
CustkeywordsPeer::getInstance()->replace(array('keyword' => 'quatre lala'), array('userid'=>2, 'ucs_id' => 22235));
$rs = $db->fetchAll("SELECT * FROM custkeywords WHERE userid= 2");
$db->dumpResultSet($rs);
*/
//$rs = $db->fetchAll("SELECT CHAR(0x4E8C USING ucs2)");
//$rs = $db->fetchAll("SELECT *, CHAR(ucs_id USING 'ucs2') AS k_ucs FROM custkeywords;");
//$db->dumpResultSet($rs);
//$aUCS = array(22235);
//DBG::out('utf8 = '.utf8::fromUnicode($aUCS));
//$aUCS = utf8::toCodePoint('四');
//DBG::out('wtf '. $aUCS);
  function getEditionKeyword($keyword)
  {
    if (strpos($keyword, rtkIndex::EDITION_SEPARATOR) > 0)
    {
      return esc_specialchars($keyword).'<br /><span class="edition">(multiple editions)</span>';
    }
    return esc_specialchars($keyword);
  }

 ?>

<?php # use action for Study page or for EditStory dialog on review page,
      # for Study page we need the kanj in the url because Save story reloads the page and URL location ?>
<?php $requestUri = $reviewMode ? 'study/editstory' : 'study/edit?id='.$kanjiData->framenum ?>

<?= form_tag($requestUri, array('name' => 'EditStory')) ?>

  <?php # state variables ?>
  <?= input_hidden_tag('ucs_code', $kanjiData->ucs_id) ?>
  <?php if ($reviewMode): ?>
    <input type="hidden" name="reviewMode" value="1" />
  <?php endif ?>

  <div id="my-story">
    <div class="padding rtkframe">

      <div class="left">
        <div class="framenum" title="Frame number"><?= $kanjiData->framenum ?></div>
<?php if ($reviewMode): ?>
        <div class="kanji onhover"><?= cjk_lang_ja($kanjiData->kanji) ?></div>
<?php else: ?>
        <div class="kanji"><?= cjk_lang_ja($kanjiData->kanji) ?></div>
<?php endif ?> 
        <div class="strokecount" title="Stroke count">[<?= $kanjiData->strokecount ?>]<br/><?= $kanjiData->readings ?></div>
      </div>

      <div class="right">
        <div class="keyword">
          <span class="JSEditKeyword" title="Click to edit the keyword" data-url="<?= $sf_context->getController()->genUrl('study/editkeyword?id='.$kanjiData->ucs_id); ?>"><?php
            # do the "multiple/edition" thing for original keywords, print the custom keyword as is.
            echo (null !== $custKeyword) ? esc_specialchars($custKeyword) : getEditionKeyword($kanjiData->keyword);
          ?></span>
        </div>

<?php $dispCss = array('none', 'block'); $b = (int)$sf_request->hasErrors(); $dispEdit = $dispCss[$b]; $dispView = $dispCss[1-$b]; ?>

        <div id="storybox">

          <?php # Story Edit ?>
          <div id="storyedit" style="display:<?= $dispEdit ?>;">

            <?= form_errors(); ?>

            <?= textarea_tag('txtStory', '', /*rows="12" cols="55"*/ array('id' => 'frmStory')) ?>

            <div class="controls valign">
              <div style="float:left;">
                <?= checkbox_tag('chkPublic', '1', false, array('id' => 'chkPublic')) ?>
                <?= label_for('chkPublic', 'Share this story') ?>
              </div>
              <div style="float:right;">
                <?= submit_tag('Save changes', array('name' => 'doUpdate', 'title' => 'Save/Update story')) ?>
                <input type="button" id="storyedit_cancel" value="Cancel" name="cancel" title="Cancel changes" />
              </div>
              <div class="clear"></div>
            </div>
          </div>
          
          <?php # Story View ?>
          <div id="storyview" style="display:<?= $dispView ?>;">
            <div id="sv-textarea" class="bookstyle" title="Click to edit your story" style="display:block;">
<?php
  if (!empty($formatted_story)) { 
    echo $formatted_story; 
    if ($isFavoriteStory) {
      echo '<br/><div class="favstory"><div class="ico">&nbsp;</div>Starred story (click to edit)</div>';
    }
  }
  else {
    echo '<div class="empty">[ click here to enter your story ]</div>';
  }
?>
            </div>

<?php if (!$reviewMode && $isRestudyKanji): ?>
  <?php if (!$isRelearnedKanji): ?>
            <div class="controls">
              <?= input_tag('doLearned', '', array(
                'type' => 'image', 'src' => '/images/2.0/study/restudy-button.gif',
                'alt'  => 'Add to restudied list',
                'title'=> 'Add kanji that you have relearned to a list for review later' )) ?>
            </div>
  <?php else: ?>
            <div class="msg-relearned">This kanji is ready for review in the <strong>restudied</strong> list.</div>
  <?php endif ?>
<?php endif ?>

          </div>
<?php #DBG::printr($sf_params->getAll()) ?>
        </div>

      </div>

    </div>
    <div class="bottom"></div>
  </div>

</form>

<?php if ($reviewMode): ?>
<div class="uiBMenu">
  <div class="uiBMenuItem">
  <?php // display a big Close button on mobile dialog (enabled by CSS)
    use_helper('Widgets'); echo ui_ibtn('Close', '', array('class' => 'uiFcBtnGreen JSDialogHide')); ?>
  </div>
</div>
<?php endif ?>

