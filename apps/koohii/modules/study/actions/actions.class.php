<?php
class studyActions extends sfActions
{

  /**
   * Study page.
   * 
   * @return 
   */
  public function executeIndex($request)
  {
  }
  
  /**
   * Study Page Search
   * 
   * Convert the search term to a framenum parameter and forward to index.
   * 
   * @url  /study/kanji/:id
   *
   */
  public function executeEdit($request)
  {
    $userId = $this->getUser()->getUserId();

    // stop bad users from overloading the database with their stupid scripts
    $throttler = new RequestThrottler($this->getUser(), 'baduser');
    $throttler->setInterval(1); // 1 seconds
    
    // searching or browsing (previous, next buttons)
    if ($request->getMethod()===sfRequest::GET)
    {

      // throttle all GET requests (browse and search)
      if (!$throttler->isValid())
      {
        $throttler->setTimeout(); // reset le timer
        $this->getResponse()->setContentType('html');
        return $this->renderPartial('misc/requestThrottleError');
      }
      $throttler->setTimeout();

      // restudy start (move to first failed card)
      if ($request->hasParameter('restudy'))
      {
        // should always have >= 1 failed card here
        $ucsId = ReviewsPeer::getNextUnlearnedKanji($userId);
        $this->forward404Unless($ucsId !== false);

        // the flag is also the restudy total
        $restudyCount = ReviewsPeer::getRestudyKanjiCount($userId);
        $this->getUser()->setAttribute(rtkUser::IS_RESTUDY_SESSION, $restudyCount);
      }
      // study
      else
      {
        // get search term from url
        $search = trim($request->getParameter('id', ''));
        
        if (!empty($search))
        {
          $search = CJK::normalizeFullWidthRomanCharacters($search);

          // replace characters that caused problems (dashes) with wildcard for SQL
          $search = str_replace('-', '%', $search);

          $ucsId = $this->getUCSForSearch($search, $userId); 
        }
      }
    }
    else
    {
      $ucsId = $this->parseUcsIdParameter($request);

      // "Add to learned list"
      if ($request->hasParameter('doLearned'))
      {
        LearnedKanjiPeer::addKanji($userId, $ucsId);

        // redirect to next restudy kanji
        $nextId = ReviewsPeer::getNextUnlearnedKanji($userId);
        if ($nextId !== false)
        {
          $kanji = KanjisPeer::getKanjiByUCS($nextId);
          $this->redirect('study/edit?id=' . $kanji->kanji);
        }
      }
    }

    // we have to test here because not every single "CJK unifed ideographs (4e00-9faf)" is guaranteed
    // to be in the database (RevTH sources characters from Kanjidic + RSH/RTH spreadsheets)
    if ($ucsId && ($this->kanjiData = KanjisPeer::getKanjiByUCS($ucsId)))
    {
      sfProjectConfiguration::getActive()->loadHelpers('CJK');

      $this->custKeyword = CustkeywordsPeer::getCustomKeyword($userId, $ucsId);

      $this->getResponse()->setTitle(
        $this->kanjiData->kanji . ' "' . ($this->custKeyword ?: $this->kanjiData->keyword) . '" - ' . _CJ('Kanji Koohii!'));

      // add request parameters for SharedStoriesListComponent view
      $request->getParameterHolder()->add(array('ucsId' => $ucsId, 'keyword' => $this->kanjiData->keyword));

      // replace search term with frame number in search box
      $request->setParameter('search', $this->kanjiData->framenum);

      // set descriptive lesson title
      $this->title = rtkIndex::getLessonTitleForIndex($this->kanjiData->framenum);

      // enable caching of Shared Stories list ONLY for Heisig indexed (~3000 items)
      if (false === rtkIndex::isExtendedIndex($this->kanjiData->framenum))
      {
        // sometimes we disable cache fin development
        if (null !== $this->getContext()->getViewCacheManager()) {
          // set cache to THIRTY days because we invalidate it whenever needed
          $this->getContext()->getViewCacheManager()->addCache(
            'study', '_SharedStories', array('withLayout' => false, 'lifeTime' => 60*60*24*30)
          );
        }
      }
    }
    else
    {
      // search gave no results
      $this->kanjiData = false;
      $this->custKeyword = null;
    }
  }

  // Handle ucs_id parameter from Study page POST requests (persists the current kanji)
  protected function parseUcsIdParameter($request)
  {
    $ucsId = $request->getParameter('ucs_code', false);

    $this->forward404Unless(BaseValidators::validateInteger($ucsId) && intval($ucsId));

    return $ucsId;
  }


  /**
   * Clear learned list, then redirect to kanji
   * 
   * study/clear?goto=< kanji | 0 >
   * 
   */
  public function executeClear($request)
  {
    LearnedKanjiPeer::clearAll($this->getUser()->getUserId());

    // redirect
    if (null !== ($gotoKanji = $request->getParameter('goto'))) {
      $this->redirect('study/edit?id=' . $gotoKanji);
    }

    $this->forward404();
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
   * @return  mixed   UCS-2 code value (integer), or false if no results.
   */
  protected function getUCSForSearch($sSearch, $userId)
  {
    $db = sfProjectConfiguration::getActive()->getDatabase();

    $s = trim($sSearch);
    //$s = preg_replace('/[^0-9a-zA-Z-\.\' \[\]\(\)]/', '', $s);
  
    if (CJK::hasKanji($s))
    {
      // it's not a western character..
      /* 0x3000 http://www.rikai.com/library/kanjitables/kanji_codes.unicode.shtml */
      
      KanjisPeer::getInstance()->select('ucs_id')->where('kanji = ?', $s)->query();
      
      return ($row = $db->fetchObject()) ? (int)$row->ucs_id : false;
    }
    elseif (preg_match('/^[0-9]+$/', $s))
    {
      // could be a Heisig #, or a unicode # (UCS2)
      return rtkIndex::getUCSForIndex($s);
    }
    elseif (preg_match('/[^0-9]/', $s))
    {
      // try to find an exact match, match before and after the RTK edition separator
      $coalesce = CustkeywordsPeer::coalesceExpr();

      $select = KanjisPeer::getInstance()->select(array('kanjis.ucs_id'));
      $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
      $select->where($coalesce . ' = ?', $s);

      // limits to selected index (RevTH only: returns correct char when simpl & trad. with same keyword)
      $select->where(rtkIndex::getSqlCol() . ' < ?', rtkIndex::RTK_UCS);  

      $select->query();

      if ($row = $db->fetchObject())
      {
        return (int)$row->ucs_id;
      }
      // minimum 3 characters for non-exact search to limit results
      elseif (strlen($s) < 3)
      {
        return false;
      }
      // otherwise just pick the first match
      else
      {
        $select->reset(coreDatabaseSelect::WHERE)
               ->where("$coalesce LIKE ?", $s.'%')
               ->query();
        return ($row = $db->fetchObject()) ? (int)$row->ucs_id : false;
      }
    }

    return false;
  }

  /**
   * Failed Kanji List.
   * 
   * @return 
   */
  public function executeFailedlist($request)
  {
  }

  /**
   * Failed Kanji List ajax table.
   * 
   * @return 
   */
  public function executeFailedlisttable($request)
  {
    $tron = new JsTron();
    return $tron->renderComponent($this, 'study', 'FailedListTable');
  }

  /**
   * Shared Stories List paging on the Study pages.
   * 
   * @return 
   */
  public function executeSharedStoriesList($request)
  {
    $tron = new JsTron();

    // throttle ajax requests to prevent script abuse
    //$response = $this->getResponse();
    $throttler = new RequestThrottler($this->getUser(), 'sharedstories');
    $throttler->setInterval(2);
    if (!$throttler->isValid())
    {
      $throttler->setTimeout();

      $tron->setError('<p>Please allow a couple seconds when paging through the stories. This '.
                      'helps us improve the website response time of the Study pages. Thanks! =)</p>');
      $tron->setStatus(JsTron::STATUS_FAILED);

      return $tron->renderJson($this);
    }
    $throttler->setTimeout();

    return $tron->renderComponent($this, 'study', 'SharedStoriesList');
  }

  /**
   * My Stories page.
   * 
   * @return 
   */
  public function executeMystories($request)
  {
    $sortkey = $request->getParameter('sort', false);
    $this->forward404Unless(!$sortkey || preg_match('/^(seq_nr|keyword|lastedit|votes|reports|public)$/', $sortkey));

    $this->sort_options = array(
      array(
        'value'        => 'seq_nr',
        'text'         => 'Frame#'
      ),
      array(
        'value'        => 'keyword',
        'text'         => 'Keyword'
      ),
      array(
        'value'        => 'lastedit',
        'text'         => 'Last Edit'
      ),
      array(
        'value'        => 'votes',
        'text'         => 'Votes'
      ),
      array(
        'value'        => 'reports',
        'text'         => 'Reports'
      ),
      array(
        'value'        => 'public',
        'text'         => 'Public'
      )
    );

    if (!$sortkey /*|| !isset($this->sort_options[$sortkey])*/)
    {
      $sortkey = 'lastedit';
    }

    $this->sort_active = $sortkey;
  }

  /**
   * My Stories ajax component (used in Study > My Stories and Profile).
   * 
   * @return 
   */
  public function executeMyStoriesTable($request)
  {
    $tron = new JsTron();

    // throttle ajax requests
    $throttler = new RequestThrottler($this->getUser(), 'sharedstories');
    $throttler->setInterval(1);
    if (!$throttler->isValid())
    {
      $throttler->setTimeout();

      $tron->setError('<p>Please allow at least one second when paging through the stories. This '.
                      'helps us improve the website response time of the Study pages. Thanks! =)</p>');
      $tron->setStatus(JsTron::STATUS_FAILED);

      return $tron->renderJson($this);
    }
    $throttler->setTimeout();

    if (0 === ($stories_uid = (int)$request->getParameter('stories_uid', 0)))
    {
      $tron->setStatus(JsTron::STATUS_FAILED);
      return $tron->renderJson($this);
    }

    $profile_page = !!$request->getParameter('profile_page', false);

    return $tron->renderComponent($this, 'study', 'MyStoriesTable', array('stories_uid' => $stories_uid, 'profile_page' => $profile_page));
  }

  /**
   * Export user's stories to CSV.
   * 
   * Note! 'col_escape' option must match the select from StoriesPeer::getSelectForExport()
   *
   */
  public function executeExport($request)
  {
    $response = $this->getResponse();
    $response->setContentType('text/plain; charset=utf-8');

    $throttler = new RequestThrottler($this->getUser(), 'study.export');
    
    if (!$throttler->isValid())
    {
    //  $response->setContentType('text/plain; charset=utf-8');
      $response->setContentType('html');
      return $this->renderPartial('misc/requestThrottleError');
    }

    // get keywords and kanji for story link substitution ({<number>})
    // RAS LE BOL je disable 
    //$this->exportKeywords = CustkeywordsPeer::getExportKeywords($this->getUser()->getUserId());

    $csv     = new ExportCSV(sfProjectConfiguration::getActive()->getDatabase());
    $select  = StoriesPeer::getSelectForExport($this->getUser()->getUserId());
    $csvText = $csv->export(
      $select, 
      array('framenr', 'kanji', 'keyword', 'public', 'last_edited', 'story'), 
      // options
      array(
        'col_escape'   => array(0, 0, 1, 0, 0, 1)
        // disable car c'est une prise de tête avec les nouveaux index
        // 'row_callback' => array($this, 'exportStoriesCallback')
      )
    );
  
    $throttler->setTimeout();
    $this->getResponse()->setFileAttachmentHeaders('my_stories.csv');
    
    $this->setLayout(false);

    return $this->renderText($csvText);
  }

  /**
   * Callback for CSV export of user's Stories.
   *
   * This callback replaces story links with the linked kanji and keyword,
   * so they are formatted similarly to the Study page.
   *
  public function exportStoriesCallback($row)
  {
    $story = preg_replace_callback('/\{(\d+)\}/', array($this, 'exportStoriesReplaceCallback'), $row[5]);
    $row[5] = $story;
    return $row;
  }

  public function exportStoriesReplaceCallback($matches)
  {
    $key = $matches[1];
    return '*'.$this->exportKeywords[$key]['keyword'].'* (FRAME '.$key.')';
  }
   */

  /**
   * EditStoryDialog ajax handler for the Review pages.
   * 
   * Request parameters:
   *   ucs_code     UCS-2 code.
   *   reviewMode   True if used from the Review page EditStory window.
   * 
   * See study/edit action (parameters) and EditStoryDialog.js
   *
   * @return 
   */
  public function executeEditstory($request)
  {
    $ucsId = $request->getParameter('ucs_code', false);
    $this->forward404Unless(BaseValidators::validateInteger($ucsId) && intval($ucsId));

    $reviewMode = $request->hasParameter('reviewMode');

    $kanjiData = KanjisPeer::getKanjiByUCS($ucsId);
    sfProjectConfiguration::getActive()->loadHelpers('CJK');

    $tron = new JsTron();
    $tron->add(array(
      'dialogTitle'   => 'Edit Story' // for '.$kanjiData->kanji.' (#'.$kanjiData->framenum.')'
    ));
    $tron->setStatus(JsTron::STATUS_PROGRESS);
//sleep( 3);

    $custKeyword = CustkeywordsPeer::getCustomKeyword($this->getUser()->getUserId(), $ucsId);

    return $tron->renderComponent($this, 'study', 'EditStory', array('kanjiData' => $kanjiData, 'reviewMode' => $reviewMode, 'custKeyword' => $custKeyword));
  }

  /**
   * EditKeyword (ajax).
   *
   * Request parameters:
   *   int   id   UCS-2 code.
   */
  public function executeEditkeyword($request)
  {
    $success = false;

    $ucsId = intval($request->getParameter('id'));

    // sanitize
    if (!BaseValidators::validateInteger($ucsId))
    {
      throw new rtkAjaxException('Bad request.');
    }

    // filter disallowed characters
    if (!CJK::isCJKUnifiedUCS($ucsId) || false === ($chardata = KanjisPeer::getKanjiByUCS($ucsId)))
    {
      throw new rtkAjaxException('Invalid character.');
    }
    
    $custom_keyword = CustkeywordsPeer::getCustomKeyword($this->getUser()->getUserId(), $chardata->ucs_id);

    $tron = new JsTron();
    $tron->add(array(
      'dialogWidth'   => 387,
      'dialogTitle'   => 'Customize Keyword for '.$chardata->kanji,
      'orig_keyword'  => $chardata->keyword,
      'cust_keyword'  => $custom_keyword
    ));
    $tron->setStatus(JsTron::STATUS_PROGRESS);

    if ($request->getMethod() !== sfRequest::POST)
    {

    }
    else
    {
      mb_internal_encoding('utf-8');

      $keyword         = trim($request->getParameter('keyword', ''));
      $default_keyword = $chardata->keyword;

      // let empty keyword revert to the default
      if ($keyword === '') {
        $keyword = $default_keyword;
      }

      if (0 === strcmp($keyword, $default_keyword))
      {
        // delete the custom keyword
        if (CustkeywordsPeer::deleteCustomKeyword($this->getUser()->getUserId(), $ucsId))
        {
          $keyword = $default_keyword;
          $success = true;
        }
      }
      else if (rtkImportKeywords::validateKeyword($keyword, $request))
      {
        // update keyword
        if (CustkeywordsPeer::updateCustomKeyword($this->getUser()->getUserId(), $ucsId, $keyword))
        {
          $success = true;
        }
        else
        {
          $request->setError('x', 'Update error.');
        }
      }
    
      // success response with edited keyword, and flag for chain editing
      if (true === $success)
      {
        $tron = new JsTron();
        $tron->setStatus(JsTron::STATUS_SUCCESS);
        $tron->set('keyword', $keyword);
          
        // chain editing
        if ($request->hasParameter('doNext'))
        {
          $tron->set('next', true);
        }

        return $tron->renderJson($this);
      }
    }

    return $tron->renderPartial($this, 'EditKeyword', array(
      'ucs_id'       => $chardata->ucs_id,
      'keyword'      => $custom_keyword !== null ? $custom_keyword : $chardata->keyword,
      'orig_keyword' => $chardata->keyword
    ));
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
   * 
   * @return 
   */
  public function executeAjax($request)
  {
    if ($request->getMethod()===sfRequest::GET)
    {
      // obsolete code
    }
    else
    {
      $sRequest = $request->getParameter('request', '');
      $sUid = $request->getParameter('uid');
      $sSid = $request->getParameter('sid');
      
      if (!preg_match('/^(star|report|copy)$/', $sRequest)
        || !BaseValidators::validateInteger($sUid)
        || !BaseValidators::validateInteger($sSid))
      {
        throw new rtkAjaxException('Badrequest');
      }
  
      if ($sRequest==='copy')
      {
        // get unformatted story with original tags for copy story feature
        $oStory = StoriesPeer::getStory($sUid, $sSid);
        if ($oStory)
        {
          StoriesPeer::useOldStoriesFix();
          $tron = new JsTron(array('text' => rtxIndexOldStoriesFix::fixOldStoriesKanjiLinks($oStory->text)));
          return $tron->renderJson($this);
        }
      }
      elseif ($sRequest === 'star' || $sRequest === 'report')
      {
        $params = (array) StoryVotesPeer::voteStory($this->getUser()->getUserId(), $sUid, $sSid, $sRequest === 'star');
        $tron = new JsTron($params);
        return $tron->renderJson($this);
      }
    }
    
    throw new rtkAjaxException('Badrequest');
  }

  /**
   * Ajax handler for Dictionary Lookup feature.
   * 
   * Request parameters:
   *
   *   ucs               UCS-2 code of the character to lookup.
   *   req_known_kanji   (OPTIONAL) Also return a string of known kanji
   *
   * Returns:
   *
   *   items             Array of vocab entries (compound, reading, etc)
   *   known_kanji       (IF "req_known_kanji") String of known kanji 
   *
   */
  public function executeDict($request)
  {
    $ucsId = intval($request->getParameter('ucs'));

    if (!CJK::isCJKUnifiedUCS($ucsId)) {
      throw new rtkAjaxException('Bad request.');
    }

    // use a TRON response because of AjaxDialog used in Flashcard Review page
    $tron = new JsTron();
    $tron->setStatus(JsTron::STATUS_PROGRESS);
    $tron->set('dialogTitle', 'Dictionary Lookup'); // for '.cjk_lang_ja($c_utf));

    $tron->set('items', $this->getDictListItems($ucsId));

    // requires known kanji list?
    if ($request->hasParameter('req_known_kanji')) {
      $tron->set('known_kanji', $this->getUser()->getUserKnownKanji());
    }

    return $tron->renderJson($this);
  }

  // get Dictionary entries for given character
  private function getDictListItems($ucsId)
  {
    $select = rtkLabs::getSelectForDictStudy($ucsId);
    $result = sfProjectConfiguration::getActive()->getDatabase()->fetchAll($select);

    $kanji = utf8::fromUnicode(array($ucsId));

    mb_regex_encoding('UTF-8');

    return $result;
  }
}
