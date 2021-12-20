<?php use_helper('CJK', 'Form', 'Widgets', 'Gadgets') ?>

<?php # Ajax loading indicator ?>
<div id="uiFcAjaxLoading" style="display:none"><span class="spinner"></span>Loading</div>

<?php # Connection timeout message ?>
<div id="uiFcAjaxError" style="display:none">
  <span class="uiFcAjaxError_msg">Oops!</span>&nbsp;&nbsp;<a href="#">Reconnect</a>
</div>

<?php # Fixes: Change Koohii Font script -- http://userscripts.org/scripts/show/6896 ?>
<div class="signin" style="display:none"><div class="m"><strong><?= $sf_user->getUsername() ?></strong></div></div>

<div class="uiFcOptions">
  <?= link_to('<span>Exit</span>', $exit_url, ['absolute' => 'true', 'class' => 'uiFcOptBtn uiFcOptBtnExit', 'title' => 'Exit flashcard review']) ?>

  <?= link_to('<span><u>B</u>ack</span>', '', ['absolute' => 'true',
    'id'    => 'JsBtnBack',
    'class' => 'uiFcOptBtn uiFcOptBtnUndo uiFcAction', 
    'title' => 'Go back one flashcard',
    'style' => 'display:none',
    'data-action' => 'back']) ?>
  <div class="clear"></div>
</div>

<div id="fr-body" class="fr-mode-vshuffle">

  <div id="rd-tops">
    <div id="uiFcProgressBar">
      <div class="uiFcStBox">
        <div class="uiFcPrBarMod">
          <?= ui_progress_bar([['value' => 0]], 100, ['id' => 'review-progress', 'borderColor' => '#5FA2D0']) ?>
        </div>
        <h3>Reviewing: <em class="count">.</em> of <em class="count">.</em></h3>
      </div>
    </div>
  </div>

  <div id="rd-main">
    <div id="uiFcReview">

      <div id="uiFcMain">
        <!-- Vue flashcard component goes here -->
      </div>
      
      <?php # flashcard anwser buttons ?>
      <div class="uiFcButtons" id="uiFcButtons">
        
        <div id="uiFcButtons0" style="display:none">
          <h3>Press Spacebar to continue</h3>
          <a href="#" id="uiFcBtnAF" class="uiIBtn uiIBtnDefault uiFcAction w-full" data-action="flip"><span><u>F</u>lip Card</span></a>
        </div>
    
        <div id="uiFcButtons1" style="display:none">
          <h3>Press Spacebar to continue</h3>
          <?= ui_ibtn('Continue', '', ['id' => 'uiFcBtnAC', 'class' => 'uiFcAction', 'data-action' => 'flip']) ?>
        </div>
      
      </div><!-- uiFcButtons -->
    </div><!-- uiFcReview -->
  </div><!-- rd-main -->


  <?php # Stats panel (displays when first card is loaded) ?>
  <div id="rd-side">
    <div id="uiFcStats" class="uiFcStats" style="display:none">

       <?= ui_ibtn('Exit', $exit_url, ['class' => 'uiIBtnGreen']); ?>

       <?= ui_ibtn('Search on google.co.jp', '', ['id' => 'search-google-jp', 'class' => 'uiIBtnGreen', 'title' => 'Search this word on Google Japan', 'target' => '_blank']); ?>

    </div><!-- uiFcStats -->    
  </div>

  <div class="clear"></div>
</div><!-- fr-body -->

<?php
  $reviewOptions = [
    // props for a (maybe/someday) Vue template
    'props' => [
      // the page to go to when clicking End with 0 reviews
      'back_url' => url_for($exit_url, true),
    ],

    'fcrOptions' => [
      'ajax_url' => $ajax_url,
      'put_request' => false,
      'items' => $items,
    ],
  ];

  $reviewMode = [
    'fc_view' => 'vocabshuffle',
  ];

  kk_globals_put('REVIEW_OPTIONS', $reviewOptions);
  kk_globals_put('REVIEW_MODE', $reviewMode);
