<?php use_helper('CJK', 'Form', 'Widgets', 'Gadgets'); ?>

<?php // Ajax loading indicator?>
<div id="uiFcAjaxLoading" style="display:none"><span class="spinner"></span>Loading</div>

<?php // Connection timeout message?>
<div id="uiFcAjaxError" style="display:none">
  <span class="uiFcAjaxError_msg">Oops!</span>&nbsp;&nbsp;<a href="#">Reconnect</a>
</div>

<?php // Fixes: Change Koohii Font script -- http://userscripts.org/scripts/show/6896?>
<div class="signin" style="display:none"><div class="m"><strong><?= $sf_user->getUsername(); ?></strong></div></div>

<div class="uiFcOptions">
  <?= link_to('<span>Exit</span>', $exit_url, [
    'absolute' => 'true',
    'class'    => 'uiFcOptBtn uiFcOptBtnExit',
    'title'    => 'Exit flashcard review',
  ]); ?>

  <?= link_to('<span><u>B</u>ack</span>', '/', [
    'absolute'    => 'true',
    'id'          => 'JsBtnBack',
    'class'       => 'uiFcOptBtn uiFcOptBtnUndo uiFcAction',
    'title'       => 'Go back one flashcard',
    'style'       => 'display:none',
    'data-action' => 'back',
  ]); ?>
  <div class="clear-both"></div>
</div>

<div class="ko-FCR-body fr-mode-vshuffle">

  <div class="ko-FCR-tops">
    <div id="uiFcProgressBar">
      <div class="ko-FcStBox max-md:p-0 max-md:bg-transparent">
        <div class="pt-0 md:pt-6">
          <?= ui_progress_bar([['value' => 0]], 100, ['id' => 'review-progress', 'borderColor' => '#5FA2D0']); ?>
        </div>
        <h3 class="ko-FcStBox-hd JSCardsCount">Reviewing: <em>.</em> of <em>.</em></h3>
      </div>
    </div>
  </div>

  <div class="ko-FCR-main">
    <div id="uiFcReview">

      <div id="uiFcMain">
        <!-- Vue flashcard component goes here -->
      </div>
      
      <?php // flashcard anwser buttons?>
      <div class="uiFcButtons" id="uiFcButtons">
        
        <div id="uiFcButtons0" style="display:none">
          <div class="uiFcButtons-prompt">Press Spacebar to continue</div>
<button
  class="ko-Btn ko-Btn--review uiFcBtnAF uiFcAction w-full"
  data-action="flip">
  <span><u>F</u>lip Card</span>
</button>
        </div>
    
        <div id="uiFcButtons1" style="display:none">
          <div class="uiFcButtons-prompt">Press Spacebar to continue</div>
<button
  class="ko-Btn ko-Btn--review uiFcBtnAC uiFcAction w-full"
  data-action="flip">
  <span>Continue</span>
</button>
        </div>
      
      </div><!-- uiFcButtons -->
    </div><!-- uiFcReview -->
  </div><!-- rd-main -->


  <?php // Stats panel (displays when first card is loaded)?>
  <div class="ko-FCR-side">
    <div class="JSFcStats" style="display:none">

       <?= link_to('Search on google.co.jp', '/',
         ['id'     => 'search-google-jp',
           'class' => 'ko-Btn ko-Btn--success block uiFcBtnAY', 'title' => 'Search this word on Google Japan', 'target' => '_blank']); ?>

    </div><!-- /JSFcStats -->    
  </div>

  <div class="clear-both"></div>
</div><!-- /ko-FCR-body -->

<?php
  $reviewOptions = [
    // props for a (maybe/someday) Vue template
    'props' => [
      // the page to go to when clicking End with 0 reviews
      'back_url' => url_for($exit_url, true),
    ],

    'fcrOptions' => [
      'ajax_url'    => $ajax_url,
      'put_request' => false,
      'items'       => $items,
    ],
  ];

$reviewMode = [
  'fc_view' => 'vocabshuffle',
];

kk_globals_put([
  'REVIEW_OPTIONS' => $reviewOptions,
  'REVIEW_MODE'    => $reviewMode,
]);
