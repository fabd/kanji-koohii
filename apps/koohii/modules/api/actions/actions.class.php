<?php
/**
 * Kanji Koohii API.
 *
 * The API uses REST requests, and JSON responses. Note the module entrypoint is NOT secure
 * and the signed in user needs to be checked.
 *
 * TODO
 *
 *  Secure REST api with HTTPS
 *   
 *    HMAC key http://php.net/manual/en/function.hash-hmac.php
 *
 */

class apiActions extends sfActions
{
  /**
   * The list of standard error codes and messages for the API.
   * 
   */
  static protected $apiErrorCodes = array(
     '95' => 'Service temporarily unavailable',
     '96' => 'User is not authenticated.',
    '100' => 'Invalid API Key',
    '110' => 'Invalid or missing API method',
    '120' => 'Login failed / Invalid session'
  );

  /**
   * Currently hardcoded (no config) valid API keys.
   *
   */
  static protected $validKeys = array(
    'TESTING'
  );

  public function executeIndex($request)
  {
    $this->redirect('api/docs');
  }

  public function executeV1($request)
  {
//DBG::request();exit;

    $api_method  = $request->getParameter('api_method', '');
    $api_method2 = $request->getParameter('api_method2', '');

    $this->forward404Unless($api_method !== '');

    if (rtkApi::isContentTypeJson($request))
    {

    }
    else
    {
      $api_key = $request->getParameter('api_key', false);

      // check for invalid or missing API key
      if (false === $api_key || false === $this->validateAPIKey($api_key)) {
        return $this->renderJson($this->createResponseFail(100));
      }
    }

    // check for missing API method
    $method = $api_method . ucfirst($api_method2);

    // check for invalid API method name
    $callable = $this->validateAPIMethod($method);
    if (false === $callable) {
      return $this->renderJson($this->createResponseFail(110));
    }

    // throttle TODO basé sur api_key ... pas sur le user (aussi pour /login par ex)
    $throttler = new RequestThrottler($this->getUser(), 'baduser');
    $throttler->setInterval(1); // seconds

    if (!$throttler->isValid())
    {
      $rsp = $this->createResponseFail(95);
    }
    else if (!$this->getUser()->isAuthenticated())
    {
      $rsp = $this->createResponseFail(96);
    }
    else
    {
      // clean up request to leave only API method parameters
      $params = $request->getParameterHolder();
      $params->remove('api_method');
      $params->remove('api_method2');
      $params->remove('api_key');
      $params->remove('action');
      $params->remove('module');

      $rsp = call_user_func($callable, $request);
    }

    $throttler->setTimeout(); // reset le timer

    $rsp->dbg_generation_time = sfProjectConfiguration::getActive()->profileEnd();

    return $this->renderJson($rsp);
  }

  public function executeDocs()
  {
  }

  protected function validateAPIKey($api_key)
  {
    return ($api_key === self::$validKeys[0]);
  }

  protected function validateAPIMethod($method)
  {
    if (!is_string($method)) {
      return false;
    }

    // create a function name
    $callable = array($this, 'API_'.$method);

    if (!is_callable($callable)) {
      return false;
    }

    return $callable;
  }

  /**
   * Similar to renderText() but this one also sets the application/json
   * content-type.
   *
   * If an object or array is passed, it will be encoded to JSON.
   *
   * Example:
   * <code>return $this->renderJson($oJson)</code>
   *
   * @param  string   Json format string to append to the response
   *
   * @return coreView::NONE
   */
  protected function renderJson($json)
  {
    if (is_object($json) || is_array($json))
    {
      $json = coreJson::encode($json);
    }

    $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');

    return $this->renderText($json);
  }

  protected function createResponseOk($rsp)
  {
    $msg = array('stat' => 'ok');

    if (rtkApi::API_DEBUG_SQL) {
      $log = sfProjectConfiguration::getActive()->getDatabase()->getDebugLog();
      //$log = "\n".implode("\n", $log);
      $msg = array_merge($msg, array(
        'sql_debug' => sprintf('%d QUERIES. *** ', count($log)) . implode('###', $log)
      ));
    }

    return (object)array_merge($msg, (array)$rsp);
  }

  protected function createResponseFail($code, $message = null)
  {
    $msg = new stdClass;

    $msg->stat = 'fail';
    $msg->code = (int)$code;

    if ($message !== null) {
      $msg->message = $message;
    }
    else {
      $msg->message = isset(self::$apiErrorCodes[$code]) ? self::$apiErrorCodes[$code] : 'Undefined';
    }

    return $msg;
  }

  /**
  protected function responseJson($response)
  {
    $json = coreJson::encode($response);
    $this->getResponse()->setContentType('application/json; charset=utf-8');
    return $this->renderText($json);
//  return $this->renderJson($response);
  }*/

  /**
   * API Methods
   */

  protected function API_accountInfo($request)
  {
    $rsp = array();

    $userId = $this->getUser()->getUserId();

    $user = $this->getUser()->getUserDetails();

    $rsp = array(
      'username' => $user['username'],
      //'email'    => $user['email'],   dont return email
      'location' => $user['location'],
      'srs_info' => array(
        'flashcard_count' => ReviewsPeer::getFlashcardCount($userId),
        'total_reviews'   => ReviewsPeer::getTotalReviews($userId)
      )
    );

    return $this->createResponseOk($rsp);
  }

  /**
   *
   *
   */
  protected function API_reviewFetch($request)
  {
    // validation
    $opt_yomi = $request->getParameter('yomi', 0);
    if (!BaseValidators::validateInteger($opt_yomi)) {
      return $this->createResponseFail(1, 'Parameter "yomi" is invalid');
    }

    $prm_items = $request->getParameter('items', '');
    $prm_items = explode(',', $prm_items);
    $items = array_filter($prm_items, 'ctype_digit');
    if (count($prm_items) === 0 || count($prm_items) !== count($items)) {
      return $this->createResponseFail(2, 'Parameter "items" is invalid or empty');
    }

    if (count($items) > rtkApi::API_REVIEW_FETCH_LIMIT) {
      return $this->createResponseFail(3, sprintf('Too many items being fetched (max: %s)', rtkApi::API_REVIEW_FETCH_LIMIT));
    }

    // flashcard format options
    $cardOpts  = new stdClass;
    if ($opt_yomi) $cardOpts->yomi = true;
    $cardOpts->api_mode = true;

    $get_cards = array();
    foreach ($items as $ucsId)
    {
      $ucsId = (int)$ucsId;
      $cardData = KanjisPeer::getFlashcardData($ucsId, $cardOpts);
        
      if ($cardData === null) {
        return $this->createResponseFail(4, sprintf('Could not fetch data for UCS code "%s"', $ucsId));
      }

      $get_cards[] = $cardData;
    }

    $rsp = array(
      'card_data' => $get_cards
    );

    return $this->createResponseOk($rsp);
  }


  /**
   *
   * Error codes:
   *   1 : Missing or invalid review mode ("mode")
   *   2 : Invalid card range (from, to)
   *   3 : Invalid type for SRS review ("type")
   */
  protected function API_reviewStart($request)
  {
    $rsp = new stdClass;

    $mode = $request->getParameter('mode');

    $rsp->card_count = 0;

    // make sure to reset SESS_CARD_UPDATED
    $uiFR = new uiFlashcardReview(array(), true);

    if ('free' === $mode)
    {
      $from    = $request->getParameter('from', 0);
      $to      = $request->getParameter('to',   0);
      $shuffle = $request->getParameter('shuffle', 0) > 0;

      $options = array();

      if (!BaseValidators::validateInteger($from) || !BaseValidators::validateInteger($to) ||
          $from > $to || $to > rtkIndex::inst()->getNumCharacters()) {
        return $this->createResponseFail(2, 'Invalid card range (from, to)');
      }

      $oFRS = new rtkFreeReviewSession(true); // only needed to create card ids
      $rsp->items = $oFRS->createFlashcardSet($from, $to, $shuffle);
    }
    else if ('srs' === $mode)
    {
      $box        = 'all';
      $type       = $request->getParameter('type', ''); // no valid default
      $filt       = '';

      if (!preg_match('/^(due|new|failed)$/', $type)) {
        return $this->createResponseFail(3, 'Invalid type for SRS review ("type")');
      }

      // translate API parameters
      if ($type === 'due') $type = 'expired';
      if ($type === 'new') $type = 'untested';
      if ($type === 'failed') { $type = 'expired'; $box = 1; }

      $rsp->items = ReviewsPeer::getFlashcardsForReview($box, $type, $filt);
    }
    else {
      return $this->createResponseFail(1, 'Missing or invalid review mode ("mode")');
    }

    $rsp->card_count = count($rsp->items);

    $rsp->limit_fetch = rtkApi::API_REVIEW_FETCH_LIMIT;
    $rsp->limit_sync  = rtkApi::API_REVIEW_SYNC_LIMIT;

    return $this->createResponseOk($rsp);
  }


  protected function API_debugSync($request)
  {
    $msg = array(
      "time" => 54812541,
      "sync" => array(
        array( "id" => 20108, "r" => 2 ),
        array( "id" => 20845, "r" => 2 )
      )
    );

    $result = rtkApi::curlJson(rtkApi::getApiBaseUrl(), $msg);

echo $result;exit;

    return $this->renderText($result);
  }

  /**
   *
   *
   * TODO    Return error message if a flashcard is non existent
   */
  protected function API_reviewSync($request)
  {
    $rsp = new stdClass;

    if ($request->getMethod() !== sfRequest::POST) {
      return $this->createResponseFail(1, 'Should be a POST request');
    }

    $body = file_get_contents("php://input");
    if ($body)
    {
      try {
        $json = coreJson::decode($body);
      } catch (Exception $e) {
        $json = null;
      }
    }

    if (!is_object($json) || !isset($json->time) || !isset($json->sync) || !is_array($json->sync)) {
      return $this->createResponseFail(2, 'Invalid request (malformed JSON, time is not set, sync is not set, sync is not array)');
    }

    if (count($json->sync) > rtkApi::API_REVIEW_SYNC_LIMIT) {
      return $this->createResponseFail(3, 'Too many items (sync limit)');
    }

// VALIDER LE TIME

    $uiFR = new uiFlashcardReview(array(
      'fn_put_flashcard' => array('ReviewsPeer', 'putFlashcardData')
    ));

    $putStatus  = new stdClass;
    $putSuccess = $uiFR->handlePutRequest($json->sync, $putStatus);

    $rsp = new stdClass;
    $rsp->put = $putSuccess;

    if (count($putStatus->ignored)) {
      $rsp->ignored = $putStatus->ignored;
    }

    return $this->createResponseOk($rsp);
  }

  protected function API_srsInfo($request)
  {
    $rsp = new stdClass;

    $user = $this->getUser();
    $userId = $user->getUserId();

//    $total_flashcards = ReviewsPeer::getFlashcardCount($userId);
 //   $rsp->reviewed_today   = ReviewsPeer::getTodayCount($userId);

    $carddata = ReviewsPeer::getLeitnerBoxCounts();

    $rsp->new_cards     = ReviewsPeer::getCountUntested($userId);
    $rsp->due_cards     = 0;
    $rsp->relearn_cards = $carddata[0]['expired_cards'];
    $rsp->learned_cards = LearnedKanjiPeer::getCount($userId);

    for ($i = 0; $i < count($carddata); $i++)
    {
      $box =& $carddata[$i];
      //$this->total_cards += $box['total_cards'];

      // dont count the red stack (expired cards in 1st box)
      if ($i > 0) {
        $rsp->due_cards += $box['expired_cards'];
      }
    }

    return $this->createResponseOk($rsp);
  }
    
  /**
  *
  * Returns ids of kanjis marked as learned
  */
  protected function API_srsLearned($request)
  {
      $rsp = new stdClass;
      $user = $this->getUser();
      $userId = $user->getUserId();

      $rsp->items = LearnedKanjiPeer::getKanji($userId);
      
      return $this->createResponseOk($rsp);
  }
    
  /**
  *
  * Returns ids of all restudy kanji plus the ids of those which are marked as learned
  * (for clients updating their study info, single rq synthesizing what they could get via multiple rq's)
  */
  protected function API_studyStart($request)
  {
      $rsp = new stdClass;
      
      $box        = 1;
      $type       = 'expired';
      $filt       = '';
      
      $rsp->items = ReviewsPeer::getFlashcardsForReview($box, $type, $filt);
      
      $user = $this->getUser();
      $userId = $user->getUserId();
      
      $rsp->learnedItems = LearnedKanjiPeer::getKanji($userId);

      return $this->createResponseOk($rsp);
  }
    
  protected function studySync($request)
  {
      $rsp = new stdClass;
      
      if ($request->getMethod() !== sfRequest::POST) {
          return $this->createResponseFail(1, 'Should be a POST request');
      }
      
      $body = file_get_contents("php://input");
      if ($body)
      {
          try {
              $json = coreJson::decode($body);
          } catch (Exception $e) {
              $json = null;
          }
      }
      
      if (!is_object($json) || !isset($json->learned) || !is_array($json->learned) !isset($json->notLearned) || !is_array($json->notLearned)) {
          return $this->createResponseFail(2, 'Invalid request (malformed JSON, learned is not set, learned is not array, notLearned is not set, notLearned is not array)');
      }
      
      if (count($json->learned) > rtkApi::API_REVIEW_SYNC_LIMIT) {
          return $this->createResponseFail(3, 'Too many learned items (sync limit)');
      }
      if (count($json->notLearned) > rtkApi::API_REVIEW_SYNC_LIMIT) {
          return $this->createResponseFail(3, 'Too many notLearned items (sync limit)');
      }
      
      $user = $this->getUser();
      $userId = $user->getUserId();

      if (!empty($json->learned)) {
          $rsp->learnedSuccess = LearnedKanjiPeer::addKanjis($userId, $json->learned);
      }
      if (!empty($json->notLearned)) {
          $rsp->notLearnedSuccess = LearnedKanjiPeer::clearKanjis($userId, $json->notLearned);
      }
      
      return $this->createResponseOk($rsp);
  }
}
