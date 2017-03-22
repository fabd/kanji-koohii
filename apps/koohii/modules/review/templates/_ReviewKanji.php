<?php use_helper('CJK', 'Form', 'Widgets', 'Gadgets') ?>

<?php # Ajax loading indicator ?>
<div id="uiFcAjaxLoading" style="display:none"><span class="spinner"></span>Loading</div>

<?php # Connection timeout message ?>
<div id="uiFcAjaxError" class="uiFcErrorMsg" style="display:none">
  Oops!&nbsp;&nbsp;<a href="#">Reconnect</a>
</div>

<?php # Fixes: Change Koohii Font script -- http://userscripts.org/scripts/show/6896 ?>
<div class="signin" style="display:none"><div class="m"><strong><?php echo $sf_user->getUsername() ?></strong></div></div>

<div id="uiFcOptions" class="uiFcOptions">
  <?php echo link_to('<span>Exit</span>', $back_url, array('absolute' => 'true', 'class' => 'uiFcOptBtn uiFcOptBtnExit', 'title' => 'Exit flashcard review')) ?>
  <a href="#" id="JsBtnHelp" class="uiFcOptBtn uiFcOptBtnHelp uiFcAction" data-action="help" title="Shows help dialog."><span>Help</span></a>
  <a href="#" class="uiFcOptBtn uiFcOptBtnStory uiFcAction" data-action="story" title="View/Edit story for this flashcard"><span><u>S</u>tory</span></a>
  <a href="#" id="JsBtnDict" class="uiFcOptBtn uiFcOptBtnDict uiFcAction" data-action="dict" title="Dictionary lookup"><span><u>D</u>ict</span></a>
  
  <?php echo link_to('<span><u>U</u>ndo</span>', '', array('absolute' => 'true',
    'id'    => 'JsBtnUndo',
    'class' => 'uiFcOptBtn uiFcOptBtnUndo uiFcAction', 
    'title' => 'Go back one flashcard',
    'style' => 'display:none',
    'data-action' => 'undo')) ?>

  <div class="clear"></div>
</div>

<div id="JsFcHelpDlg" style="display:none">
  <div class="bd">
    <div class="uiFcHelpDlg body JSDialogClose">
      <h3>Keyboard Shortcuts</h3>

      <p><kbd>SPACE</kbd> or <kbd>F</kbd> or <kbd>Numpad 0</kbd> to flip card.</p>
      <p><kbd>N</kbd> <kbd>H</kbd> <kbd>Y</kbd> <kbd>E</kbd> for <strong>N</strong>o, <strong>H</strong>ard, <strong>Y</strong>es, <strong>E</strong>asy.</p>
      <p>... and <kbd>1</kbd> <kbd>2</kbd> <kbd>3</kbd> on the main keyboard.</p>
      <p>... and <kbd>1</kbd> <kbd>2</kbd> <kbd>3</kbd> on the numeric keypad.</p>
      <p><kbd>K</kbd> or <kbd>,</kbd> (numpad) to <strong>skip</strong> this card.</p>
      <p><kbd>S</kbd> to open/close the <strong>Edit Story</strong> window.</p>
      <p><kbd>D</kbd> to open/close the <strong>Dictionary</strong> window.</p>
      <p><kbd>U</kbd> to <strong>undo</strong> the last answer.</p>

    </div>
  </div>
</div>

<div id="fr-body">

  <div id="rd-tops">
    <div id="uiFcProgressBar">
      <div class="uiFcStBox">
        <div class="uiFcPrBarMod">
          <?php echo ui_progress_bar(array(array('value' => 0)), 100, array('id' => 'review-progress', 'borderColor' => '#5FA2D0')) ?>
        </div>
        <h3>Reviewing: <em class="count">.</em> of <em class="count">.</em></h3>
      </div>
    </div>
  </div>

  <?php 
    $rdMainClass = array();
    if ($fc_yomi) { $rdMainClass[] = 'with-yomi'; }
  ?>
  <div id="rd-main" class="<?php echo implode(' ', $rdMainClass) ?>">
    <div id="uiFcReview" class="uiFcReview">

      <!-- flashcard container -->
      <div id="uiFcMain" class="uiFcCard uiFcAction <?php echo $fc_reverse ? 'is-reverse':'' ?>" data-action="flip" style="display:none">
        
        <?php if (!$freemode) {
          # Edit Flashcard Menu
          $dialogUri  = $sf_context->getController()->genUrl('flashcards/dialog');
          $params     = escape_once(coreJson::encode(array('review' => 1))); // flag for the Edit Flashcard Menu dialog
        ?>
        <a id="uiFcMenu" href="#" title="Edit Flashcard" class="uiGUI uiFcAction" data-action="fcmenu" data-uri="<?php echo $dialogUri ?>" data-param="<?php echo $params ?>"><i class="fa fa-bars"></i></a>
        <?php } ?>

        <div id="keyword" class="fcData fcData-keyword">&nbsp;</div>
        <div id="kanjibig">
          <p>
            <?php echo cjk_lang_ja('&nbsp;', array('fcData', 'fcData-kanji')) ?>
  <?php if (CJ_HANZI): ?>
            <div id="pinyin" class="fcData fcData-pinyin">&nbsp;</div>
  <?php endif ?>
          </p>
        </div>
        <div id="strokecount" title="Stroke count"><?php echo cjk_lang_ja('&#30011;&#25968;', array('kanji')) ?> <span class="fcData fcData-strokecount">&nbsp;</span></div>
        <div id="framenum" class="fcData fcData-framenum">&nbsp;</div>
        
  <?php if ($fc_yomi): ?>
        <div id="uiFcYomi">
          <div class="pad">
            <div class="yomi y_o">
              <div><?php echo cjk_lang_ja('&nbsp;', array('class' => 'vyc')) ?><?php echo cjk_lang_ja('&nbsp;', array('class' => 'vyr')) ?></div>
              <div class="vyg">&nbsp;</div>
            </div>
            <div class="yomi y_k">
              <div><?php echo cjk_lang_ja('&nbsp;', array('class' => 'vyc')) ?><?php echo cjk_lang_ja('&nbsp;', array('class' => 'vyr')) ?></div>
              <div class="vyg">&nbsp;</div>
            </div>
          </div>
        </div>
  <?php endif ?>

      </div><!-- uiFcMain -->

      <div class="uiFcButtons" id="uiFcButtons">
        
        <div id="uiFcButtons0" style="display:none">
          <h3>Press Spacebar or F to flip card</h3>
          <?php #echo ui_ibtn('<u>F</u>lip Card', $routename, array('id' => 'uiFcBtnAF', 'class' => 'uiFcAction', 'data-action' => 'flip')) ?>
          <a href="#" id="uiFcBtnAF" class="uiIBtn uiIBtnDefault uiFcAction" data-action="flip"><span><u>F</u>lip Card</span></a>
        </div>
    
        <div id="uiFcButtons1"<?php echo $freemode ? '' : ' class="three-buttons"'; ?> style="display:none">
          <h3>Do you remember this <?php echo _CJ('kanji') ?>?</h3>
          <?php 
echo ui_ibtn('<u>N</u>o', '', array('id' => 'uiFcBtnAN', 'class' => 'uiIBtnRed uiFcAction', 'data-action' => 'no', 'title' => 'Forgotten'));
if (!$freemode) {
echo ui_ibtn('<u>H</u>ard', '', array('id' => 'uiFcBtnAH', 'class' => 'uiIBtnOrange uiFcAction', 'data-action' => 'hard', 'title' => 'Hard'));
}
echo ui_ibtn('<u>Y</u>es', '', array('id' => 'uiFcBtnAY', 'class' => 'uiFcAction', 'data-action' => 'yes', 'title' => 'Remembered with some effort'));
if (!$freemode) {
  echo ui_ibtn('<u>E</u>asy', '', array('id' => 'uiFcBtnAE', 'class' => 'uiFcAction', 'data-action' => 'easy', 'title' => 'Remembered easily'));
}
          ?>
          <div class="clear"></div>
        </div>
        
      </div><!-- uiFcButtons -->
    </div><!-- uiFcReview -->
  </div><!-- rd-main -->


  <?php # Stats panel (displays when first card is loaded) ?>
  <div id="rd-side">
    <div id="uiFcStats" class="uiFcStats" style="display:none">

      <div id="uiFcPiles" class="stacks">
        <div class="td stack" title="Cards remembered"><i class="fa fa-check"></i><span class="JsPass">0</span></div>
        <div class="td stack" title="Cards forgotten"><i class="fa fa-close"></i><span class="JsFail">0</span></div>
      </div>
      
      <div id="uiFcEnd" class="">
        <a href="#" class="uiFcStBox JsFinish uiFcAction" data-action="end" title="Finish - go to review summary">End</a>
      </div>

  <div class="clear"></div>

      <div id="uiFcStDeld" class="uiFcStBox" style="display:none">
        <h3>Deleted: <em class="count">0</em></h3>
        <p id="uiFcStDeldK"><?php echo cjk_lang_ja('&nbsp;') ?></p>
      </div>

    </div><!-- uiFcStats -->    
  </div>

  <div class="clear"></div>
</div><!-- fr-body -->

<?php # Form to redirect to Review Summary with POST ?>
<form method="post" id="uiFcRedirectForm" action="<?php echo url_for('@review_summary') ?>" style="display:none">
  <?php # Custom data to pass to the Review Summary (review.js onEndReview()) ?>
  <?php echo input_hidden_tag('ts_start', $ts_start) ?>
  <input type="hidden" name="fc_pass" value="0" />
  <input type="hidden" name="fc_fail" value="0" />
  <input type="hidden" name="fc_deld" value="0" />
  <input type="hidden" name="fc_free" value="<?php echo (int)$freemode ?>" />
<?php if ($freemode): ?>
  <input type="hidden" name="fc_rept" value="<?php echo $fc_rept ?>" />
<?php endif ?>
</form>

<div id="mobile-debug" style="padding:20px 0 0;"></div>

<?php koohii_onload_slot() ?>
var options =
{
  end_url:        "<?php echo url_for('@review_summary', true) ?>",
  editstory_url:  "<?php echo url_for('study/editstory') ?>",
  dictlookup_url: "<?php echo url_for('study/dict') ?>",

  yomi:           <?php var_export($fc_yomi) ?>,

  fcr_options:
  {
    // flashcard format options
    params:       {yomi: <?php echo intval($fc_yomi) ?>},

    //num_prefetch: 10,
    ajax_url:     "<?php echo $sf_data->get('ajax_url', ESC_JS_NO_ENTITIES) ?>",
    back_url:     "<?php echo url_for($back_url, true) ?>",
    items:        [<?php echo implode(',', $sf_data->getRaw('items')) ?>]
  }
};

App.ready(function(){
  App.KanjiReview.initialize(options);

<?php /*
  var div = document.getElementById("mobile-debug");
  div.innerHTML = '<pre style="color:#4a9dd7">(this will be removed soon)' + "\n" +
    'document client w/h = ' + document.documentElement.clientWidth + 'x' + document.documentElement.clientHeight + "\n" +
    'document inner  w/h = ' + window.innerWidth + 'x' + window.innerHeight+"\n" +
    'screen ........ w/h = ' + screen.width+'x'+screen.height+"\n"+
    'document offset w/h = ' + document.documentElement.offsetWidth + 'x' + document.documentElement.offsetHeight + "\n" +
    '</pre>';
*/ ?>
});
<?php end_slot() ?>
