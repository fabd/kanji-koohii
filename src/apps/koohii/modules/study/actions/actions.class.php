<?php
/**
 * Study module actions.
 *
 * Methods:
 *   executeIndex($request)
 *   executeEdit($request)
 *   getLessonTitleForIndex($frameNr)
 *   executeClear($request)
 *   executeFailedlist($request)
 *   executeFailedlisttable($request)
 *   executeSharedStoriesList($request)
 *   executeMystories($request)
 *   executeMyStoriesTable($request)
 *   executeEditstory($request)
 *   executeEditkeyword($request)
 *   executeAjax($request)
 *   executeDict($request)
 *   executeVocabpick($request)
 *   executeVocabdelete($request)
 *
 * Private:
 *   getUCSForSearch($sSearch, $userId)
 *   getDictListItems($ucsId)
 *
 * @property bool         $isBeginRestudy
 * @property false|object $kanjiData
 * @property string       $title
 * @property array        $sort_options
 * @property string       $sort_active
 */
class studyActions extends sfActions
{
  /**
   * Study page.
   */
  public function executeIndex(coreRequest $request) {}

  /**
   * Study Page Search.
   *
   * Convert the search term to a framenum parameter and forward to index.
   *
   * @url  /study/kanji/:id
   */
  public function executeEdit(coreRequest $request)
  {
    $userId = kk_get_user()->getUserId();
    $ucsId  = false;

    // stop bad users from overloading the database with their stupid scripts
    $throttler = new RequestThrottler(kk_get_user(), 'baduser');
    $throttler->setInterval(1); // 1 seconds

    // searching or browsing (previous, next buttons)
    if ($request->getMethod() === sfRequest::GET) {
      // throttle all GET requests (browse and search)
      if (!$throttler->isValid()) {
        $throttler->setTimeout(); // reset le timer
        $this->getResponse()->setContentType('html');

        return $this->renderPartial('misc/requestThrottleError');
      }
      $throttler->setTimeout();

      // restudy start (move to first failed card)
      if ($request->hasParameter('restudy')) {
        // should always have >= 1 failed card here
        $ucsId = ReviewsPeer::getNextUnlearnedKanji($userId);
        $this->forward404Unless($ucsId !== false);

        // show the "Learned" pane
        $this->isBeginRestudy = true;
      }
      // study
      else {
        // get search term from url
        $search = trim($request->getParameter('id', ''));

        if (!empty($search)) {
          $search = CJK::normalizeFullWidthRomanCharacters($search);

          // replace characters that caused problems (dashes) with wildcard for SQL
          $search = str_replace('-', '%', $search);

          $ucsId = $this->getUCSForSearch($search, $userId);
        }
      }
    } else {
      $ucsId = rtkValidators::sanitizeCJKUnifiedUCS($request->getParameter('ucs_code') ?? 0);

      // "Add to learned list"
      if ($request->hasParameter('doLearned')) {
        LearnedKanjiPeer::addKanji($userId, $ucsId);

        // if user navigates from the Restudy List, goes back there
        if (rtkValidators::sanitizeBool($request->getParameter('fromRestudyList'))) {
          $this->redirect('study/failedlist');
        }

        // redirect to next restudy kanji
        $nextId = ReviewsPeer::getNextUnlearnedKanji($userId);
        if ($nextId !== false) {
          $kanji = KanjisPeer::getKanjiByUCS($nextId);
          if ($kanji !== false) {
            $this->redirect('study/edit?id='.$kanji->kanji);
          }
        }
      }
    }

    // we have to test here because not every single "CJK unifed ideographs (4e00-9faf)" is guaranteed
    // to be in the database (RevTH sources characters from Kanjidic + RSH/RTH spreadsheets)
    if ($ucsId && ($this->kanjiData = KanjisPeer::getKanjiByUCS($ucsId))) {
      sfProjectConfiguration::getActive()->loadHelpers('CJK');

      // add request parameters for SharedStoriesListComponent view
      $request->getParameterHolder()->add(['ucsId' => $ucsId, 'keyword' => $this->kanjiData->keyword]);

      // replace search term with frame number in search box
      $request->setParameter('search', (string) $this->kanjiData->framenum);

      // set descriptive lesson title
      $this->title = $this->getLessonTitleForIndex($this->kanjiData->framenum);

      // enable caching of Shared Stories list ONLY for Heisig indexed (~3000 items)
      if (false === rtkIndex::isExtendedIndex($this->kanjiData->framenum)) {
        // sometimes we disable cache in development
        if (null !== ($cacheManager = $this->getContext()->getViewCacheManager())) {
          // set cache to THIRTY days because we invalidate it whenever needed
          $cacheManager->addCache(
            'study',
            '_SharedStories',
            [
              'withLayout' => false,
              'lifeTime'   => 60 * 60 * 24 * 30,
            ]
          );
        }
      }
    } else {
      // search gave no results
      $this->kanjiData = false;
    }
  }

  /**
   * TODO   Refactor this code into the sequence-specific classes.
   *
   * @param mixed $frameNr
   *
   * @return string
   */
  public function getLessonTitleForIndex($frameNr)
  {
    $lessNr = rtkIndex::getLessonForIndex($frameNr);

    $title = '';

    if ($lessNr && $lessNr <= rtkIndex::inst()->getNumLessonsVol1()) {
      $title = "Lesson {$lessNr}";
    } elseif ($lessNr === 57) {
      $title = 'RTK Volume 3';
    } elseif ($lessNr === 58) {
      $title = 'RTK1 Supplement';
    } else {
      $title = 'Character not in '.rtkIndex::getSequenceName();
    }

    return $title;
  }

  /**
   * Clear learned list, then redirect.
   *
   * Because of the redirect, browser history doesn't keep
   * this step, so the user can go "Back" without repeating this action.
   */
  public function executeClear(coreRequest $request)
  {
    LearnedKanjiPeer::clearAll(kk_get_user()->getUserId());

    $goto    = $request->getParameter('goto');
    $routeTo = $goto === 'restudy' ? 'study/failedlist' : 'study/kanji/1';

    $this->redirect($routeTo);
  }

  /**
   * Parse the search box text for a character (Heisig index, CJK character,
   * unicode code point "extended" index). Returns the UCS-2 code.
   *
   * The search term should be an exact keyword or match the beginning of a keyword.
   *
   * The multiple edition keyword separator (/) should be replaced with a wildcard (%) beforehand.
   *
   * The wildcard (%) can be used one or more times. A wildcard (%) is always added at the end
   * of the search term.
   *
   * @param mixed $sSearch
   * @param mixed $userId
   *
   * @return mixed UCS-2 code value (integer), or false if no results
   */
  protected function getUCSForSearch($sSearch, $userId)
  {
    $db = kk_get_database();

    $s = trim($sSearch);
    // $s = preg_replace('/[^0-9a-zA-Z-\.\' \[\]\(\)]/', '', $s);

    if (CJK::hasKanji($s)) {
      // it's not a western character..
      /* 0x3000 http://www.rikai.com/library/kanjitables/kanji_codes.unicode.shtml */

      KanjisPeer::getInstance()->select('ucs_id')->where('kanji = ?', $s)->query();

      return ($row = $db->fetchObject()) ? (int) $row->ucs_id : false;
    }
    if (preg_match('/^[0-9]+$/', $s)) {
      // could be a Heisig #, or a unicode # (UCS2)
      return rtkIndex::getUCSForIndex((int) $s);
    }
    if (preg_match('/[^0-9]/', $s)) {
      // try to find an exact match, match before and after the RTK edition separator
      $coalesce = CustkeywordsPeer::coalesceExpr();

      $select = KanjisPeer::getInstance()->select(['kanjis.ucs_id']);
      $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
      $select->where($coalesce.' = ?', $s);

      // limits to selected index (RevTH only: returns correct char when simpl & trad. with same keyword)
      $select->where(rtkIndex::getSqlCol().' < ?', rtkIndex::RTK_UCS);

      $select->query();

      if ($row = $db->fetchObject()) {
        return (int) $row->ucs_id;
      }
      // minimum 3 characters for non-exact search to limit results
      if (strlen($s) < 3) {
        return false;
      }
      // otherwise just pick the first match
      else {
        $select->reset(coreDatabaseSelect::WHERE)
          ->where("{$coalesce} LIKE ?", $s.'%')
          ->query()
        ;

        return ($row = $db->fetchObject()) ? (int) $row->ucs_id : false;
      }
    }

    return false;
  }

  /**
   * Failed Kanji List.
   */
  public function executeFailedlist(coreRequest $request) {}

  /**
   * Failed Kanji List ajax table.
   */
  public function executeFailedlisttable(coreRequest $request)
  {
    $tron = new JsTron();
    $tron->setHtml($this->getComponent('study', 'FailedListTable'));

    return $this->renderJson($tron);
  }

  /**
   * Shared Stories List paging on the Study pages.
   */
  public function executeSharedStoriesList(coreRequest $request)
  {
    $tron = new JsTron();

    // throttle ajax requests to prevent script abuse
    // $response = $this->getResponse();
    $throttler = new RequestThrottler(kk_get_user(), 'sharedstories');
    $throttler->setInterval(2);
    if (!$throttler->isValid()) {
      $throttler->setTimeout();

      $tron->addError('<p>Please allow a couple seconds when paging through the stories. This '
                      .'helps us improve the website response time of the Study pages. Thanks! =)</p>');
      $tron->setStatus(JsTron::STATUS_FAILED);

      return $this->renderJson($tron);
    }
    $throttler->setTimeout();

    $tron->setHtml($this->getComponent('study', 'SharedStoriesList'));

    return $this->renderJson($tron);
  }

  /**
   * My Stories page.
   */
  public function executeMystories(coreRequest $request)
  {
    // use Last Edit as the default sort
    $sortkey = $request->getParameter(uiSelectTable::QUERY_SORTCOLUMN, 'lastedit');

    $this->forward404If(
      $sortkey
      && !preg_match('/^(seq_nr|keyword|lastedit|votes|reports|public)$/', $sortkey)
    );

    $this->sort_options = [
      [
        'value' => 'seq_nr',
        'text'  => 'Frame#',
      ],
      [
        'value' => 'keyword',
        'text'  => 'Keyword',
      ],
      [
        'value' => 'lastedit',
        'text'  => 'Last Edit',
      ],
      [
        'value' => 'votes',
        'text'  => 'Votes',
      ],
      [
        'value' => 'reports',
        'text'  => 'Reports',
      ],
      [
        'value' => 'public',
        'text'  => 'Public',
      ],
    ];

    $this->sort_active = $sortkey;
    $request->setParameter(uiSelectTable::QUERY_SORTCOLUMN, $sortkey);
  }

  /**
   * My Stories ajax component (used in Study > My Stories and Profile).
   */
  public function executeMyStoriesTable(coreRequest $request)
  {
    $tron = new JsTron();

    // throttle ajax requests
    $throttler = new RequestThrottler(kk_get_user(), 'sharedstories');
    $throttler->setInterval(1);
    if (!$throttler->isValid()) {
      $throttler->setTimeout();

      $tron->addError('<p>Please allow at least one second when paging through the stories. This '
                      .'helps us improve the website response time of the Study pages. Thanks! =)</p>');
      $tron->setStatus(JsTron::STATUS_FAILED);

      return $this->renderJson($tron);
    }
    $throttler->setTimeout();

    if (0 === ($stories_uid = (int) $request->getParameter('stories_uid'))) {
      $tron->setStatus(JsTron::STATUS_FAILED);

      return $this->renderJson($tron);
    }

    $profile_page = (bool) $request->getParameter('profile_page');

    $tron->setHtml($this->getComponent('study', 'MyStoriesTable', ['stories_uid' => $stories_uid, 'profile_page' => $profile_page]));

    return $this->renderJson($tron);
  }

  /**
   * EditStory ajax handler (EditStoryDialog & EditStory Vue component).
   *
   * GET
   *   ucsCode            number
   *   reviewMode         boolean    True if used from the Review page (EditStory dialog)
   *
   * POST
   *   ucsCode            number
   *   postStoryEdit      string
   *   postStoryPublic    boolean
   *   reviewMode         boolean
   *
   * See study/edit action (parameters) and EditStoryDialog.js
   */
  public function executeEditstory(coreRequest $request)
  {
    // FIXME - temporary compat. with AjaxDialog (YUI2 Connect) in Flashcard Review page
    if ($request->hasParameter('ucsCode')) {
      // pretend we received application/json in POST body
      $json = (object) [
        'ucsCode'    => $request->getParameter('ucsCode'),
        'reviewMode' => true,
      ];
    } else {
      $json = $request->getContentJson();
    }

    $tron = new JsTron();

    $userId     = kk_get_user()->getUserId();
    $ucsId      = rtkValidators::sanitizeCJKUnifiedUCS($json->ucsCode);
    $reviewMode = (bool) $json->reviewMode;

    $storedStory          = StoriesPeer::getStory($userId, $ucsId);
    $storyCurrentlyShared = $storedStory && (bool) $storedStory->public;

    if ($request->getMethod() === sfRequest::GET) {
      $postStoryEdit = ($storedStory ? $storedStory->text : '');

      // STATE (load state for the "Edit Story" Vue comp in flashcard page)
      $tron->add([
        'initStoryEdit'   => $postStoryEdit,
        'initStoryPublic' => (bool) ($storedStory && $storedStory->public),
      ]);

      // Flashcard Review page feature -- get "favorite" story, if user's edit story is empty
      if (!$storedStory && $reviewMode) {
        if (false !== ($favStory = StoriesPeer::getFavouriteStory($userId, $ucsId))) {
          // the "favorite" story to format
          $postStoryEdit = $favStory->text;

          // the user's own story is empty, if editing
          $tron->set('initStoryEdit', '');
          $tron->set('initFavoriteStory', true);
        }
      }
    } else {
      // STATE
      $postStoryEdit   = trim($json->postStoryEdit);
      $postStoryPublic = (bool) $json->postStoryPublic;

      // disallow markup
      if ($postStoryEdit !== strip_tags($postStoryEdit)) {
        $tron->addError('HTML markup (tags) formatting not allowed in stories.');

        return $this->renderJson($tron);
      }
      // $this->forward404();

      // validate kanji links within story
      if (true !== ($errorMsg = rtkStory::validateKanjiLinks($postStoryEdit))) {
        $tron->addError($errorMsg);

        return $this->renderJson($tron);
      }

      // delete story if empty text
      if (empty($postStoryEdit)) {
        StoriesPeer::deleteStory($userId, $ucsId);
        $postStoryEdit = '';
      } else { // update story
        // validate story length BEFORE substitutions (to match "x chars left" feedback on the client side)
          mb_internal_encoding('utf-8');
        $count = mb_strlen($postStoryEdit);
        if ($count > rtkStory::MAXIMUM_STORY_LENGTH) {
          $n = $count - rtkStory::MAXIMUM_STORY_LENGTH;
          $tron->addError(sprintf('Story is too long (512 characters maximum, %d over the limit).', $n));

          return $this->renderJson($tron);
        }

        // NOTE! it's assumed kanji substitution makes the story SMALLER (eg. "{1000}" => "{類}")
        $postStoryEdit = rtkStory::substituteKanjiLinks($postStoryEdit);

        if (true !== StoriesPeer::updateStory($userId, $ucsId, ['text' => $postStoryEdit, 'public' => (int) $postStoryPublic])) {
          $tron->addError("Woops, the story couldn't be saved. Try again in a few moments.");

          return $this->renderJson($tron);
        }
      }

      // invalidate cache -- approx 7% of stories are public,
      //  so skipping cache invalidation is worthwhile if possible
      // error_log(sprintf("public %d > %d", $storyCurrentlyShared, $postStoryPublic));
      if ($postStoryPublic || $storyCurrentlyShared) {
        // error_log(sprintf("invalidating the cache"));
        StoriesSharedPeer::invalidateStoriesCache($ucsId);
      }

      if (!$reviewMode) {
        // these are used for visual feedback, adding or removing the story from Shared Stories list
        $isStoryShared = $postStoryEdit !== '' && $postStoryPublic;
        $tron->set('isStoryShared', $isStoryShared);

        $tron->set('sharedStoryId', "story-{$userId}-{$ucsId}");
        sfProjectConfiguration::getActive()->loadHelpers(['Tag', 'Url', 'Links']);
        $tron->set('sharedStoryAuthor', link_to_member(kk_get_user()->getUserName()));
      }
    }

    // keyword to auto-format
    $kanjiData     = KanjisPeer::getKanjiByUCS($ucsId);
    $custKeyword   = CustkeywordsPeer::getCustomKeyword($userId, $ucsId);
    $formatKeyword = $custKeyword ?? $kanjiData->keyword;

    // initial load (from Flashcard Review's edit story dialog)
    $tron->add([
      'kanjiData'   => $kanjiData,
      'custKeyword' => $custKeyword,
    ]);

    // POST state
    $tron->add([
      'initStoryView' => StoriesPeer::getFormattedStory($postStoryEdit, $formatKeyword, true),
    ]);

    // sleep(1);

    return $this->renderJson($tron);
  }

  /**
   * Endpoint for the EditKeyword dialog.
   *
   * /study/editkeyword/id/{ucsId}
   *
   * GET
   *   id     int      UCS-2 code
   *
   * POST request parameters (JSON):
   *   ucsId    int     UCS-2 code
   *   keyword  string
   */
  public function executeEditkeyword(coreRequest $request)
  {
    // legacy AjaxPanel GET request doesn't use JSON
    if ($request->hasParameter('id')) {
      $json = (object) [
        'ucsId' => $request->getParameter('id'),
      ];
    } else {
      $json = $request->getContentJson();
    }

    // sleep(1);
    $ucsId = (int) $json->ucsId;

    // filter disallowed characters
    $chardata = KanjisPeer::getKanjiByUCS($ucsId);
    if (!CJK::isCJKUnifiedUCS($ucsId) || false === $chardata) {
      throw new rtkAjaxException('Invalid character.');
    }

    $custom_keyword = CustkeywordsPeer::getCustomKeyword(kk_get_user()->getUserId(), $chardata->ucs_id);

    $tron = new JsTron();

    if ($request->getMethod() !== sfRequest::POST) {
      // GET request when Edit Keyword dialog opens
      $tron->add([
        'ucs_id'       => $chardata->ucs_id,
        'orig_keyword' => $chardata->keyword,
        'user_keyword' => $custom_keyword ?? $chardata->keyword,
        'max_length'   => rtkImportKeywords::MAX_KEYWORD,
      ]);
    } else {
      mb_internal_encoding('utf-8');

      $success = false;

      $keyword         = trim($json->keyword);
      $default_keyword = $chardata->keyword;

      // let empty keyword revert to the default
      if ($keyword === '') {
        $keyword = $default_keyword;
      }

      if (0 === strcmp($keyword, $default_keyword)) {
        // delete the custom keyword
        if (CustkeywordsPeer::deleteCustomKeyword(kk_get_user()->getUserId(), $ucsId)) {
          $keyword = $default_keyword;
          $success = true;
        }
      } else {
        $is_valid = rtkImportKeywords::validateKeyword($keyword, $request);

        // add validation errors to the TRON response
        if ($request->hasErrors()) {
          $tron->addErrors($request->getErrors());
          $tron->setStatus(JsTron::STATUS_FAILED);
        }

        if ($is_valid) {
          if (CustkeywordsPeer::updateCustomKeyword(kk_get_user()->getUserId(), $ucsId, $keyword)) {
            $success = true;
          } else {
            $tron->addError('Oops, update failed.');
            $tron->setStatus(JsTron::STATUS_FAILED);
          }
        }
      }

      // success response with edited keyword, and flag for chain editing
      if (true === $success) {
        $tron->setStatus(JsTron::STATUS_SUCCESS);
        $tron->set('keyword', $keyword);

        // chain editing
        if ($request->hasParameter('doNext')) {
          $tron->set('next', true);
        }
      }
    }

    return $this->renderJson($tron);
  }

  /**
   * Ajax handler for Shared Stories component.
   *
   * Post:
   *
   *   request     "star": star story
   *               "report": report story
   *               "copy": copy story
   *   uid         Story author's userid
   *   sid         Story id (kanji's UCS-2 code value)
   */
  public function executeAjax(coreRequest $request)
  {
    $json = $request->getContentJson();

    // request parameters
    $sRequest = $json->request;
    $sUid     = BaseValidators::sanitizeInteger($json->uid);
    $sSid     = BaseValidators::sanitizeInteger($json->sid);

    // $this->forward404Unless(preg_match('/^(star|report|copy)$/', $sRequest));

    if ($sRequest === 'copy') {
      // get unformatted story with original tags for copy story feature
      $oStory = StoriesPeer::getStory($sUid, $sSid);
      if ($oStory) {
        StoriesPeer::useOldStoriesFix();
        $tron = new JsTron([
          'storyText' => rtxIndexOldStoriesFix::fixOldStoriesKanjiLinks($oStory->text),
        ]);

        return $this->renderJson($tron);
      }
    } elseif ($sRequest === 'star' || $sRequest === 'report') {
      // [ uid, sid, vote, lastvote, stars, kicks ]
      $params = (array) StoryVotesPeer::voteStory(kk_get_user()->getUserId(), $sUid, $sSid, $sRequest === 'star');
      $tron   = new JsTron($params);

      return $this->renderJson($tron);
    }

    throw new rtkAjaxException('Badrequest');
  }

  /**
   * Ajax handler for Dictionary Lookup feature.
   *
   * JSON request:
   *
   *   ucs               UCS-2 code of the character to lookup.
   *   reqKnownKanji   (OPTIONAL) Also return a string of known kanji
   *
   * Returns:
   *
   *   items             Array of vocab entries (compound, reading, etc)
   *   picks             Array of user's selected vocab ([dictid, ...])
   *   knownKanji       (IF "reqKnownKanji") String of known kanji
   */
  public function executeDict(coreRequest $request)
  {
    $json = $request->getParamsAsJson();
    // DBG::printr($json);exit;

    $ucsId = rtkValidators::sanitizeCJKUnifiedUCS($json->ucs);

    $tron   = new JsTron();
    $userId = kk_get_user()->getUserId();

    $tron->set('items', $this->getDictListItems($ucsId));
    $tron->set('picks', VocabPicksPeer::getUserPicks($userId, $ucsId));

    if (true === $json->reqKnownKanji) {
      $tron->set('knownKanji', kk_get_user()->getUserKnownKanji());
    }

    return $this->renderJson($tron);
  }

  // get Dictionary entries for given character, use cached data if possible
  private function getDictListItems($ucsId)
  {
    $data = [];

    $DictEntryArray = CacheDictLookupPeer::getDictListForUCS($ucsId);

    // use the slower method if no cached results (ie. not a RTK kanji)
    if (false === $DictEntryArray) {
      // error_log("Not Dict Cache for UCS {$ucsId}");
      $select         = rtkLabs::getSelectForDictStudy($ucsId);
      $DictEntryArray = kk_get_database()->fetchAll($select);
    }

    return $DictEntryArray;
  }

  /**
   * User selected a vocab entry in DictList component (could be study or review page).
   *
   * JSON request:
   *
   *   ucs               UCS-2 code of associated character
   *   dictid            JMDICT entseq id
   *
   * Returns:
   */
  public function executeVocabpick(coreRequest $request)
  {
    $json = $request->getContentJson();

    $ucsId  = rtkValidators::sanitizeCJKUnifiedUCS($json->ucs);
    $dictId = BaseValidators::sanitizeInteger($json->dictid);

    $userId = kk_get_user()->getUserId();

    $tron = new JsTron();

    if (true !== VocabPicksPeer::link($userId, $ucsId, $dictId)) {
      $tron->addError('Oops, update failed.');
      $tron->setStatus(JsTron::STATUS_FAILED);
    }

    return $this->renderJson($tron);
  }

  public function executeVocabdelete(coreRequest $request)
  {
    $json = $request->getContentJson();

    $ucsId = rtkValidators::sanitizeCJKUnifiedUCS($json->ucs);

    $userId = kk_get_user()->getUserId();

    $tron = new JsTron();

    if (true !== VocabPicksPeer::unlink($userId, $ucsId /* , $dictId */)) {
      $tron->addError('Oops, delete failed.');
      $tron->setStatus(JsTron::STATUS_FAILED);
    }

    return $this->renderJson($tron);
  }
}
