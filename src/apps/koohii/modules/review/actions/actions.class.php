<?php

class reviewActions extends sfActions
{
  /**
   * Review graph page.
   *
   * @param sfRequest $request
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

  /**
   * Custom Review modes 
   *
   * @param sfRequest $request
   */
  public function executeCustom($request)
  {
    $userId = sfContext::getInstance()->getUser()->getUserId();
    $this->knowncount = ReviewsPeer::getReviewedFlashcardCount($userId, LeitnerSRS::FAILEDSTACK + 1);
    $this->knowndefault = max($this->knowncount, 1);

    // set defaults for forms
    $request->setParameter('shuffle', 1);
  }

  public function executeVocab($request)
  {
    $userId = sfContext::getInstance()->getUser()->getUserId();
    $this->learnedcount = ReviewsPeer::getReviewedFlashcardCount($userId, rtkLabs::VOCABSHUFFLE_MINBOX);
  }

  /**
   * Review graph filter actions.
   *
   * @param sfRequest $request
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

    if ($filter == 'all')
    {
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
   *
   * @param sfRequest $request
   */
  public function executeFree($request)
  {
    $this->forward('review', 'review');
  }

  /**
   * Kanji Flashcard review page with FlashcardReview.
   *
   * Custom Review modes:
   *
   *   from, to     Range of Heisig numbers (1-xxxx)
   *   lesson       Lesson Id (sets from, to)
   *   known        N cards to review from known kanji.
   *   from_text    String of unique kanji to create deck from
   *
   * Options:
   *
   *   shuffle      Shuffle cards (with from, to)
   *   reverse      Kanki to Keyword
   *
   * SRS mode:
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

    $reviewFrom = (int) $request->getParameter('from', 0);
    $reviewTo = (int) $request->getParameter('to', 0);
    $reviewKnown = (int) $request->getParameter('known', 0);
    $reviewFromText = $request->getParameter('from_text', '');
    $reviewShuffle = !!$request->getParameter('shuffle', 0);
    $reviewReverse = !!$request->getParameter('reverse', 0);
    // DBG::request();exit;
    
    // if any of these options is set it is assumed to be a Custom Review (not SRS)
    $isCustomReview = $reviewFrom > 0 || $reviewKnown > 0 || $reviewFromText;

    if ($lessonId = (int) $request->getParameter('lesson', 0))
    {
      $lessonInfo = rtkIndex::getLessonData($lessonId);
      $this->forward404If(!$lessonInfo);
      $reviewFrom = $lessonInfo['lesson_from'];
      $reviewTo = $lessonInfo['lesson_from'] + $lessonInfo['lesson_count'] - 1;
    }

    if (false === $isCustomReview)
    {
      // SRS review
      $reviewBox = $request->getParameter('box', 'all');
      $reviewType = $request->getParameter('type', 'expired');
      $reviewFilt = $request->getParameter('filt', '');
      $reviewMerge = $request->getParameter('merge') ? true : false;

      // validate
      $this->forward404Unless(preg_match('/^(all|[0-9]+)$/', $reviewBox));
      $this->forward404Unless(preg_match('/^(expired|untested|relearned|fresh|known)$/', $reviewType));
      $this->forward404Unless($reviewFilt == '' || preg_match('/(rtk1|rtk3)/', $reviewFilt));

      // pick title
      //$this->setReviewTitle($reviewType, $reviewFilt);

      // options for setting up the review template
      $options['items'] = ReviewsPeer::getFlashcardsForReview($reviewBox, $reviewType, $reviewFilt, $reviewMerge);

      $options['ajax_url'] = $this->getController()->genUrl('review/ajaxsrs');
    }
    else
    {
      $options['ajax_url'] = $this->getController()->genUrl('review/ajaxfree');
      $options['fc_rept'] = null;

      if ($request->hasParameter('known'))
      {
        // free review :: known cards

        $this->forward404If(!BaseValidators::validateInteger($reviewKnown)
                            || !BaseValidators::validateNotEmpty($reviewKnown)
                            || $reviewKnown <= 0, 'Invalid card range');
        $cards = ReviewsPeer::getFlashcardsForReview('all', 'known', '');
        $options['items'] = array_slice($cards, 0, $reviewKnown);
        //DBG::printr($options['items']);exit;

        // NO repeat button because the randomized card set can change
        $options['fc_rept'] = null;
      }
      else if ($reviewFromText) {
        // Custom Review : Create a Review Deck from Japanese Text

        $chars = CJK::getKanji($reviewFromText);
        $this->forward404Unless(count($chars) > 0, 'from_text is invalid');

        // just in case client didn't remove the duplicates
        $uniqueChars = array_unique($chars);
        
        $cards = array_map(fn($char) => mb_ord($char), $uniqueChars);

        if ($reviewShuffle) {
          shuffle($cards);
        }

        $options['items'] = $cards;

        // set the options to repeat the review at the Review Summary screen
        $options['fc_rept'] = json_encode([
          'action' => $this->getController()->genUrl('review/free'),
          'from_text' => implode($uniqueChars),
          'reverse' => (int) $reviewReverse,
          'shuffle' => (int) $reviewShuffle,
        ], JSON_UNESCAPED_UNICODE);
        // DBG::printr($options);exit;
      }
      else
      {
        // Custom Review : by index or lesson (from/to)

        $this->forward404If(!BaseValidators::validateInteger($reviewFrom), 'Invalid card range');
        $this->forward404If(!BaseValidators::validateInteger($reviewTo), 'Invalid card range');
        $this->forward404If($reviewFrom > $reviewTo || $reviewTo > rtkIndex::inst()->getNumCharacters(), 'Invalid card range');

        $options['items'] = rtkIndex::createFlashcardSet($reviewFrom, $reviewTo, $reviewShuffle);
        
        // set the options to repeat the review at the Review Summary screen
        $options['fc_rept'] = json_encode([
          'action' => $this->getController()->genUrl('review/free'),
          'from' => $reviewFrom,
          'to' => $reviewTo,
          'reverse' => (int) $reviewReverse,
          'shuffle' => (int) $reviewShuffle,
        ]);
      }
    }
    
    // additional options passed to the front end
    $options['freemode'] = $isCustomReview;
    
    $options['ts_start'] = UsersPeer::intLocalTime();
    
    $options['fc_reverse'] = $reviewReverse;

    // route for Exit button and 'empty' review url
    $options['exit_url'] = $isCustomReview ? 'review/custom' : '@overview';

    FlashcardReview::getInstance()->start();

    // these will be variables in the review template partial
    $this->reviewOptions = $options;
  }

  /**
   * Review Summary (SRS and Custom Reviews)
   *
   * POST (from form in _ReviewKanji.php which is submitted at end of review):
   *
   *   ts_start
   *   fc_deld
   *   fc_free      Is "1" if Custom Review mode (not SRS)
   * 
   * If Custom Review mode (not SRS):
   *
   *   fc_rept      JSON encoded POST params to repeat the review
   * 
   * @param sfRequest $request
   */
  public function executeSummary($request)
  {
    // free mode review flag
    $this->fc_free = $request->getParameter('fc_free', 0);
    $this->fc_rept = $request->getParameter('fc_rept', '');
  
    // deleted cards
    $deletedCards = $request->getParameter('fc_deld');
    $this->deletedCards = $deletedCards ? explode(',', $deletedCards) : [];

    // POST request is initiated by the end of a review session
    if ($request->getMethod() === sfRequest::POST)
    {
      // validate post parameters
      $validator = new coreValidator($this->getContext()->getActionName());
      $this->forward404Unless($validator->validate($request->getParameterHolder()->getAll()));

      // (SRS only): update last review info for the "Who's Reviewing" table
      if (!$this->fc_free)
      {
        ActiveMembersPeer::updateFlashcardInfo($this->getUser()->getUserId());
      }
    }
  }

  /**
   * Ajax handler for SRS flashcard reviews.
   *
   * @param sfRequest $request
   */
  public function executeAjaxsrs($request)
  {
    $options = [
      'fn_get_flashcard' => 'KanjisPeer::getKanjiCardData',
      'fn_put_flashcard' => 'ReviewsPeer::putFlashcardData',
    ];

    return $this->handleFlashcardRequest($request, $options);
  }

  /**
   * Ajax handler for Free mode reviews.
   *
   * @param sfRequest $request
   */
  public function executeAjaxfree($request)
  {
    $options = [
      'fn_get_flashcard' => 'KanjisPeer::getKanjiCardData',
      'fn_put_flashcard' => 'reviewActions::dummyUpdate',
    ];
    //debugging
    // sleep(6);
    // $response = sfContext::getInstance()->getResponse();
    // $response->setStatusCode(500);

    return $this->handleFlashcardRequest($request, $options);
  }

  /**
   * fn_put_flashcard handler for flashcard answers from the free review mode.
   *
   * THIS IS A DUMMY HANDLER  for  FlashcardReview to cache answers.
   *
   * @param int    $id    Flashcard id (UCS-2 code value)
   * @param object $oData Flashcard answer data
   *
   * @return bool Returns true if update went succesfully
   */
  public static function dummyUpdate($id, $oData)
  {
    assert(!empty($id) && isset($oData->r));

    return true;
  }

  /**
   * handleFlashcardRequest.
   *
   * @see  FlashcardReview.php for POST request parameters.
   *
   * @param sfRequest $request
   * @param mixed     $options
   */
  private function handleFlashcardRequest($request, $options)
  {
    $fcrData = json_decode($request->getParameter('json', '{}'));

    if (empty($fcrData))
    {
      throw new rtkAjaxException('Empty JSON Request.');
    }

    $flashcardReview = FlashcardReview::getInstance()->config($options);

    return $this->renderJson($flashcardReview->handleRequest($fcrData));
  }

  /**
   * Sets template variable for the Review session title.
   *
   * protected function setReviewTitle($reviewType, $reviewFilt)
   * {
   * $titles = array(
   * 'expired'   => 'Due flashcards',
   * 'untested'  => 'New flashcards',
   * 'relearned' => 'Relearned flashcards',
   * 'fresh'     => 'Undue flashcards'
   * );
   * $this->title = isset($titles[$reviewType]) ? $titles[$reviewType] : 'Flashcards';
   * }
   *
   * @param sfRequest $request
   */

  /**
   * Summary Table ajax.
   *
   * @return
   */
  public function executeSummaryTable($request)
  {
    $ts_start = $request->getParameter('ts_start', 0);
    $this->forward404Unless(BaseValidators::validateInteger($ts_start));
    $tron = new JsTron();

    return $tron->renderComponent($this, 'review', 'summaryTable', ['ts_start' => $ts_start]);
  }
}
