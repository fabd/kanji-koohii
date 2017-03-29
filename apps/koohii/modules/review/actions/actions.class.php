<?php
class reviewActions extends sfActions
{
  /**
   * Review graph page.
   * 
   * @return 
   */
  public function executeIndex($request)
  {
    // set local pref default value
    $this->filter = $this->getUser()->getLocalPrefs()->sync('review.graph.filter', null, '');
    if ($this->filter == '')
    {
      $this->filter = 'all';
    }
  
    $this->flashcard_count = ReviewsPeer::getFlashcardCount($this->getUser()->getUserId());
  }
  
  public function executeCustom($request)
  {
    $userId = sfContext::getInstance()->getUser()->getUserId();
    $this->knowncount   = ReviewsPeer::getReviewedFlashcardCount($userId, LeitnerSRS::FAILEDSTACK + 1);
    $this->knowndefault = max($this->knowncount, 1);
  }

  public function executeVocab($request)
  {
    $userId = sfContext::getInstance()->getUser()->getUserId();
    $this->learnedcount = ReviewsPeer::getReviewedFlashcardCount($userId, rtkLabs::VOCABSHUFFLE_MINBOX);
  }

  /**
   * Review graph filter actions.
   * 
   * @return 
   */
  public function executeAjaxLeitnerGraph($request)
  {
    $filter = $request->getParameter('filter', '');
    if (!preg_match('/^(all|rtk1|rtk3)$/', $filter))
    {
      throw new rtkAjaxException('Invalid parameters.');
    }

    if ($filter=='all') {
      $filter = '';
    }
    
    // remember last filter selected
    $this->getUser()->getLocalPrefs()->sync('review.graph.filter', $filter, '');

    $tron = new JsTron();
    return $tron->renderComponent($this, 'review', 'LeitnerChart');
  }

  /**
   * Kanji Flashcard review WITHOUT SRS (no flashcard updates in database).
   *
   * See executeReview for parameters.
   */
  public function executeFree($request)
  {
    $this->forward('review', 'review');
  }

  /**
   * Kanji Flashcard review page with uiFlashcardReview
   * 
   * Free Review Mode (Labs page):
   *
   *   from, to     Range of Heisig numbers (1-xxxx)
   *    yomi        Include example words (optional)
   *   shuffle      Shuffle cards (with from, to)
   *   reverse      Kanki to Keyword
   *
   *   known        N cards to review from known kanji.
   *
   * Otherwise the SRS parameters are expected:
   * 
   *   type = 'expired'|'untested'|'relearned'|'fresh'
   *   box  = 'all'|[1-5]
   *   filt = ''|'rtk1'|'rtk3'
   *   
   * @param object $request
   */
  public function executeReview($request)
  {
    $this->setLayout('fullscreenLayout');

    // if 'from' is not specified, then it is assumed to be a SRS review
    $reviewFrom   = $request->getParameter('from', 0);
    $reviewTo     = $request->getParameter('to',   0);
    $reviewKnown  = $request->getParameter('known', 0);
    $reviewShuffle= $request->getParameter('shuffle', 0) > 0;
//DBG::request();exit;

    // kanji > keyword
    $options['fc_reverse'] = $request->getParameter('reverse') ? true : false;
    
    // flag to indentify free review mode
    $options['freemode'] = $reviewFrom > 0 || $reviewKnown > 0;

    $options['ts_start'] = UsersPeer::intLocalTime();

    if (false === $options['freemode'])
    {
      // in SRS mode the yomi option comes from user's account settings
      $options['fc_yomi'] = $this->getUser()->getUserSetting('OPT_READINGS');

      // SRS review
      $reviewBox  = $request->getParameter('box', 'all');
      $reviewType = $request->getParameter('type', 'expired');
      $reviewFilt = $request->getParameter('filt', '');
      $reviewMerge= $request->getParameter('merge') ? true : false;

      // validate
      $this->forward404Unless( preg_match('/^(all|[1-9]+)$/', $reviewBox) );
      $this->forward404Unless( preg_match('/^(expired|untested|relearned|fresh|known)$/', $reviewType) );
      $this->forward404Unless( $reviewFilt=='' || preg_match('/(rtk1|rtk3)/', $reviewFilt) );

      // pick title
      //$this->setReviewTitle($reviewType, $reviewFilt);

      // options for setting up the review template
      $options['items'] = ReviewsPeer::getFlashcardsForReview($reviewBox, $reviewType, $reviewFilt, $reviewMerge);

      $options['ajax_url'] = $this->getController()->genUrl('review/ajaxsrs');
    }
    else
    {
      $oFRS = new rtkFreeReviewSession(true);

      $options['fc_yomi']    = $request->getParameter('yomi') ? true : false;

      if ($request->hasParameter('known'))
      {
        // free review :: known cards

        $this->forward404If(!BaseValidators::validateInteger($reviewKnown) ||
                            !BaseValidators::validateNotEmpty($reviewKnown) ||
                            $reviewKnown <= 0, 'Invalid card range');
        $cards = ReviewsPeer::getFlashcardsForReview('all', 'known', '');
        $options['items'] = array_slice($cards, 0, $reviewKnown);
//DBG::printr($options['items']);exit;

        // repeat button URL disable because the randomized card set can change
        $options['fc_rept'] = ''; //$this->getController()->genUrl('review/free?known='.$reviewKnown.'&yomi='.$options['fc_yomi'], true);
      }
      else
      {
        // free review :: fixed range

        $this->forward404If(!BaseValidators::validateInteger($reviewFrom), 'Invalid card range');
        $this->forward404If(!BaseValidators::validateInteger($reviewTo), 'Invalid card range');
        $this->forward404If($reviewFrom > $reviewTo || $reviewTo > rtkIndex::inst()->getNumCharacters(), 'Invalid card range');

        $options['items'] = $oFRS->createFlashcardSet($reviewFrom, $reviewTo, $reviewShuffle);

        // repeat button URL
        $options['fc_rept'] = $this->getController()->genUrl(
          implode('&',array(
          'review/free?from='.$reviewFrom,
          'to='.$reviewTo,
          'yomi='.intval($options['fc_yomi']),
          'shuffle='.intval($reviewShuffle),
          'reverse='.intval($options['fc_reverse'])
          )), true
        );
      }

      $options['ajax_url'] = $this->getController()->genUrl('review/ajaxfree');
    }

    // route for Exit button and 'empty' review url
    $options['back_url'] = $options['freemode'] ? 'review/custom' : '@overview';

    $this->uiFR = new uiFlashcardReview($options, true);
    
    // these will become the review template partial options
    $this->reviewOptions = $options;
  }

  /**
   * Ajax handler for SRS flashcard reviews.
   *
   */
  public function executeAjaxsrs($request)
  {
    $options = array(
      'fn_get_flashcard' => array('KanjisPeer', 'getFlashcardData'),
      'fn_put_flashcard' => array('ReviewsPeer', 'putFlashcardData')
    );

    return $this->handleFlashcardRequest($request, $options);
  }

  /**
   * Ajax handler for Free mode reviews.
   *
   */
  public function executeAjaxfree($request)
  {
    $options = array(
      'fn_get_flashcard' => array('KanjisPeer', 'getFlashcardData'),
      'fn_put_flashcard' => array('reviewActions', 'freeReviewUpdate')
    );

    return $this->handleFlashcardRequest($request, $options);
  }

  /**
   * fn_put_flashcard handler for flashcard answers from the free review mode.
   * 
   * @param  int      $id     Flashcard id (UCS-2 code value)
   * @param  object   $oData  Flashcard answer data
   *
   * @return boolean  Returns true if update went succesfully
   * 
   */
  public static function freeReviewUpdate($id, $oData)
  {
    if ($id < 1 || !isset($oData->r) || !preg_match('/^[1-3]$/', $oData->r))
    {
      throw new sfException(__METHOD__." Invalid parameters ($id)");
    }

    // udpate session for the review summary
    $oFRS = new rtkFreeReviewSession();
    $oFRS->updateFlashcard($id, (int)$oData->r);

    return true;
  }

  /**
   * handleFlashcardRequest
   * 
   * @see  uiFlashcardReview.php for POST request parameters.
   */
  private function handleFlashcardRequest($request, $options)
  {
    $oJson = coreJson::decode($request->getParameter('json', '{}'));

    if (!empty($oJson))
    {
      $flashcardReview = new uiFlashcardReview($options);
      $this->getResponse()->setContentType('application/json');
      return $this->renderText( $flashcardReview->handleJsonRequest($oJson) );
    }

    throw new rtkAjaxException('Empty JSON Request.');
  }

  /**
   * Sets template variable for the Review session title
   * 
  protected function setReviewTitle($reviewType, $reviewFilt)
  {
    $titles = array(
      'expired'   => 'Due flashcards',
      'untested'  => 'New flashcards',
      'relearned' => 'Relearned flashcards',
      'fresh'     => 'Undue flashcards'
    );
    $this->title = isset($titles[$reviewType]) ? $titles[$reviewType] : 'Flashcards';
  }
   */

  /**
   * Summary Table ajax
   * 
   * @return 
   */
  public function executeSummaryTable($request)
  {
    $ts_start = $request->getParameter('ts_start', 0);
    $this->forward404Unless(BaseValidators::validateInteger($ts_start));
    $tron = new JsTron();
    return $tron->renderComponent($this, 'review', 'summaryTable', array('ts_start' => $ts_start));
  }
}
