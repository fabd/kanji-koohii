<?php
/**
 * Rtk Kanji Flashcards Peer.
 * 
 * This table stores the review status of all kanji flashcards for all users.
 * 
 * Methods:
 *  getFlashcardData($userId, $ucsId)
 *  getFlashcardsByIndex($userId, $filter = null)
 *  getFlashcardCount()
 *  getKnownKanji()
 *  getTodayCount()
 *  getCountExpired()
 *  getCountUntested()
 *  getLeitnerBoxCounts()
 *  getReviewedFlashcardCount()
 *  getTotalReviews()
 *  getMostRecentReviewTimeStamp()
 *  getSelectForDetailedList()
 *  getSelectForExport()
 *  getFlashcardsForReview()
 
 *  getHeisigProgressCount()
 *  getProgressChartData()
 * 
 *  getRestudyKanjiCount($userId)
 *  getRestudyKanjiListSelect($userId)
 *
 *  hasFlashcard($userId, $ucsId)
 *  isFailedCard($userId, $ucsId)
 *
 *  putFlashcardData($id, $oData)
 *  failFlashcard($userId, $ucsId)
 *
 *  addSelection()
 *  deleteSelection()
 *  filterExistingCards($userId, array $cards)
 *
 * Private:
 *  addFlashcards()    Add/delete at once with a SQL statement.
 *  deleteFlashcards()
 *
 * Helpers:
 *  filterByRtk()      Applies framenum filter to the select object.
 *  filterByUserId()   Applies userid filter to the select object.
 * 
 * 
 * @author  Fabrice Denis
 */

class ReviewsPeer extends coreDatabaseTable
{
  protected $tableName = 'reviews';

  /**
   * This function must be copied in each peer class.
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Returns flashcard status for given user and character id.
   * 
   * @param int $userId
   * @param int $ucsId
   * @return array|false  Row data
   */
  public static function getFlashcardData(int $userId, int $ucsId)
  {
    assert($ucsId > 0x3000);

    $select = self::getInstance()->select([
        '*',
        'ts_lastreview' => 'UNIX_TIMESTAMP(lastreview)'
      ])
      ->where('ucs_id = ?', $ucsId);
    $select = self::filterByUserId($select, $userId);
    $select->query();

    $db = self::getInstance()->getDb();
    return $db->fetch();
  }

  /**
   * Return user's kanji card data for use by the front end. Only data for
   * existing flashcards is returned! (no empty/default card values)
   * 
   * Optionally filter the returned card data to a set of UCS codes.
   *
   * @param int $userId
   * @param int[]|null $ucsIds a set of flashcard ids, null for all RTK1 cards
   * @return array
   */
  public static function getUserKanjiCardsJS(int $userId, ?array $ucsIds = null)
  {
    $select = self::getInstance()->select([
      'ucs' => 'reviews.ucs_id',
      'box' => 'leitnerbox',
      'tot' => 'totalreviews',
    ]);

    $select = self::filterByUserId($select, $userId);
    
    if ($ucsIds !== null) {
      $select = KanjisPeer::joinLeftUsingUCS($select);
      $select->whereIn('ucs_id', $ucsIds);
    }
    else
    {
      // For Old Editions, make sure to include the range #3008-3030 so we can
      // show Lesson 58 (RTK Supplement) on the Progress Chart.
      // FIXME? includes RTK3 kanjis (lesson 57) which we don't currently display.
      $select = self::filterByRtk($select, 'rtk1+3');
    }

// DBG::out($select);exit;
    $rows = self::getInstance()->getDb()->fetchAll($select);

    $cards = [];

    // format the data for use as `new Map(data)`
    foreach ($rows as $row) {
      $ucs = (int) $row['ucs'];

      // Leitner box
      $box = (int) $row['box'];

      // is it a new card? (use 0/1 as boolean shorter JSON output)
      $new = (int) ($row['tot'] === '0' && $box === 1);

      $cards[] = [
        $ucs, [
          'ucs' => $ucs,
          'box' => $box,
          'new' => $new,
        ]
      ];
    }

    return $cards;
  }

  /**
   * Returns true if user has a flashcard for given character UCS code.
   * 
   * @param int   $userId 
   * @param int   $ucsId
   *
   * @return bool
   */
  public static function hasFlashcard($userId, $ucsId)
  {
    $count = self::getInstance()->count('userid = ? AND ucs_id = ?', [$userId, $ucsId]);
    return (bool)$count;
  }

  /**
   * Checks if a flashcard is "failed" (in the red stack/box 1)
   * 
   * @param  int   $userId
   * @param  int   $ucsId    UCS-2 code value.
   *
   * @return boolean
   */
  public static function isFailedCard($userId, $ucsId)
  {
    $cardData = self::getFlashcardData($userId, $ucsId);

    return (
      is_array($cardData)
      && $cardData['leitnerbox'] == 1
      && $cardData['totalreviews'] > 0
    );
  }

  /**
   * Returns an array of flashcard ids in the user's deck, as *indexes*.
   *
   * Note: if filter is not used, cards outside of the active sequence will be
   * returned, which have an *extended frame number* (UCS codes).
   * 
   * @param  int    $userId
   * @param  string $filter  See filterByRtk()
   * 
   * @return array  Array of frame numbers, empty array if no cards
   */
  public static function getFlashcardsByIndex($userId, $filter = '')
  {
    $select = self::getInstance()->select(['seq_nr' => rtkIndex::getSqlCol()]);
    
    // join Kanjis table just once
    if ($filter === '')
    {
      $select = KanjisPeer::joinLeftUsingUCS($select);
    }
    else
    {
      $select = self::filterByRtk($select, $filter);
    }

    $select = self::filterByUserId($select, $userId);

    return self::getInstance()->getDb()->fetchCol($select);
  }
  
  /**
   * Do a COUNT(*) on the select object, return integer result.
   * 
   * @param  int    $userId  User id.
   * @param  coreDatabaseSelect  $select   Select object with where clause(s) applied.
   * 
   * @return int
   */
  private static function _getFlashcardCount($userId, $select = null)
  {
    $db = self::getInstance()->getDb();

    if (is_null($select))
    {
      $select = self::getInstance()->select();
    }

    $select->columns(['count' => 'COUNT(*)']);
    $select = self::filterByUserId($select, $userId);
// DBG::printr($select->__toString());exit;
    $select->query();
    $result = $db->fetchObject();
    return (int) $result->count;
  }

  /**
   * Return count of flashcards for user.
   * 
   * @param int $userId
   * @param string $filter  See filterByRtk()
   * 
   * @return int
   */
  public static function getFlashcardCount($userId, $filter = '')
  {
    $select = self::getInstance()->select();
    $select = self::filterByRtk($select, $filter);

    return self::_getFlashcardCount($userId, $select);
  }

  /**
   * Returns a string of all kanji known by user. Currently 'known' simply
   * means the user has a flashcard for it.
   * 
   * @return string
   */
  public static function getKnownKanji($userId)
  {
    $user = sfContext::getInstance()->getUser();

    // get array of known kanji as ucs ids (this simple SELECT uses INDEX)
    $select = self::getInstance()->select()->columns('ucs_id');
    $select = self::filterByUserId($select, $userId);
    $ucs_array = self::getInstance()->getDb()->fetchCol($select);

    // convert to utf8 string for storage
    $knownKanji = count($ucs_array) ? utf8::fromUnicode($ucs_array) : '';

    return $knownKanji;
  }

  /**
   * Return count of flashcards reviewed today (midnight time).
   *
   */
  public static function getTodayCount($userId)
  {
    $user = sfContext::getInstance()->getUser();
    $select = self::getInstance()->select()->where('lastreview > DATE('.UsersPeer::sqlLocalTime().')');
    return self::_getFlashcardCount($userId, $select);
  }

  /**
   * Return count of expired flashcards (except failed cards) for user.
   * 
   * @param
   * 
   * @return
   */
  public static function getCountExpired($userId)
  {
    $user = sfContext::getInstance()->getUser();
    $sqlLocalTime = new coreDbExpr(UsersPeer::sqlLocalTime());
    $select = self::getInstance()->select()->where('totalreviews>0 AND leitnerbox>1  AND expiredate <= ?', $sqlLocalTime);
    return self::_getFlashcardCount($userId, $select);
  }

  /**
   * Return count of untested flashcards for user.
   * 
   * @param  string  $filter  See filterByRtk()
   * 
   * @return
   */
  public static function getCountUntested($userId, $filter = '')
  {
    $user = sfContext::getInstance()->getUser();
    $select = self::getInstance()->select()->where('totalreviews <= 0');
    $select = self::filterByRtk($select, $filter);
    return self::_getFlashcardCount($userId, $select);
  }

  /**
   * Return flashcard counts for Leitner boxes.
   * 
   * Returns
   *
   *     [
   *       'expired_cards' => 20,
   *       'fresh_cards'   => 10,
   *       'total_cards'   => 30
   *     ],
   * 
   * @param  string   $filter  Filter by RtK Volume 1, 3, or all (see filterByRtk())
   * 
   * @return array   Array of box data (including empty boxes), index 0 = failed/new cards.
   */
  public static function getLeitnerBoxCounts($filter = '')
  {
    $user = sfContext::getInstance()->getUser();
    $userId = $user->getUserId();

    $select = self::getInstance()->select([
        'box'   => 'leitnerbox',
        'due'   => sprintf('(%s >= expiredate)', UsersPeer::sqlLocalTime()),
        'count' => 'COUNT(*)'
      ])
      ->where('totalreviews > 0')
      ->group(['leitnerbox', 'due ASC']);
    
    $select = self::filterByUserId($select, $userId);
    $select = self::filterByRtk($select, $filter); // FIXME  we don't strictly need sequences JOIN here
    $rows   = self::getInstance()->getDb()->fetchAll($select);

    // do not assume a fixed box setting, do assume SQL data is not messed up
    $highest_box = count($rows) ? max(array_column($rows, 'box')) : 1;

    $boxes = [];
    for ($i = 0; $i < $highest_box; $i++) {
      $boxes[$i] = ['expired_cards' => 0, 'fresh_cards' => 0, 'total_cards' => 0];
    }

    // set due & undue counts
    foreach ($rows as $row) {
      $boxNr = (int) ($row['box'] - 1);

      assert($boxNr >= 0, "LeitnerBox number is invalid!");
      if ($boxNr < 0) {
        throw new sfException("Invalid LeitnerBox number: {$row['box']} for user {$userId}");
      }

      $pile = $row['due'] ? 'expired_cards' : 'fresh_cards';
      $boxes[$boxNr][$pile] += $row['count'];
    }

    // set totals per box
    for ($i = 0; $i < $highest_box; $i++) {
      $boxes[$i]['total_cards'] = $boxes[$i]['expired_cards'] + $boxes[$i]['fresh_cards'];
    }
    
    return $boxes;
  }

  /**
   * Returns number of kanji flashcards with at least one review.
   *
   * @param  int     $minBox  Minimum leitnerbox (optional)
   * 
   * @return int
   */
  public static function getReviewedFlashcardCount($userId, $minBox = 0)
  {
    $condition = $minBox ? 'totalreviews > 0 AND leitnerbox >= '.$minBox : 'totalreviews > 0';

    $select = self::getInstance()->select()->where($condition);
    return self::_getFlashcardCount($userId, $select);
  }
 
  /**
   * Return total reviews accross all kanji,
   * for the Profile page.
   * 
   * @return int
   */
  public static function getTotalReviews($userId)
  {
    $db = self::getInstance()->getDb();
    $select = $db->select(['count' => 'SUM(totalreviews)'])->from('reviews');
    self::filterByUserId($select, $userId)->query();
    $row = $db->fetchObject();
    return (int)$row->count;
  }

  /**
   * Return the most recent flash card review timestamp
   * (of a single flash card, not a review session).
   * 
   * @param  int    $userId   User id.
   * @return mixed  Lastest review timestamp, or FALSE
   */
  public static function getMostRecentReviewTimeStamp($userId)
  {
    $select = self::getInstance()->select('MAX(lastreview)');
    $select = self::filterByUserId($select, $userId);
    $ts_lastreview = self::getInstance()->getDb()->fetchOne($select);
    return !is_null($ts_lastreview) ? $ts_lastreview : false;
  }

  /**
   * Returns Select for detailed flashcard lists.
   * 
   * Used on:
   * - Detailed Flashcard List
   * - Manage Flashcards > Select flashcards to remove (RemoveListTableComponent)
   * 
   * @param
   * @return
   */
  public static function getSelectForDetailedList($userId)
  {
    $select = self::getInstance()->select([
      'kanjis.ucs_id', 'seq_nr' => rtkIndex::getSqlCol(), 'failurecount', 'successcount', 'leitnerbox',
      'ts_lastreview' => 'UNIX_TIMESTAMP(lastreview)', 'kanji', 'onyomi', 'strokecount',
      'tsLastReview' => 'UNIX_TIMESTAMP(lastreview)'
      ]);
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select = self::filterByUserId($select, $userId);
    $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
    return $select;
  }

  /**
   * Returns Select for Manage > Edit Keywords table.
   * 
   * @return coreDatabaseSelect
   */
  public static function getSelectForEditKeywordsList($userId)
  {
    $select = self::getInstance()->select([
      'kanjis.ucs_id', 'kanji', 'seq_nr' => rtkIndex::getSqlCol(),
      'kanjis.keyword', 'custkeyword' => 'custkeywords.keyword',
      'ts_lastreview' => 'UNIX_TIMESTAMP(lastreview)'
      ]);
    $select = KanjisPeer::joinLeftUsingUCS($select);
    
    // don't use addCustomKeywordJoin() here because we want BOTH original & custom keyword
    $select->joinLeftUsing('custkeywords', ['ucs_id', 'userid']);
    
    $select = self::filterByUserId($select, $userId);

    return $select;
  }
  
  /**
   * Returns select for flashcard export feature.
   * 
   * @param int  $userId
   */
  public static function getSelectForExport($userId)
  {
    // the order of columns must match the ExportCSV call in executeExportflashcards() ! 
    $select = self::getInstance()->select([
      'seq_nr' => rtkIndex::getSqlCol(), 'kanji',
      'keyword' => CustkeywordsPeer::coalesceExpr(),
      'lastreview', 'expiredate', 'leitnerbox', 'failurecount', 'successcount',
      // vocab added with PR #287 (works because we currently have 1 word per card limit)
      'compound'
    ]);
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select = CustkeywordsPeer::addCustomKeywordJoinUsing($select);
    $select = VocabPicksPeer::addVocabPicksLeftJoinUsing($select);
    $select->order('seq_nr', 'ASC');
    $select = self::filterByUserId($select, $userId);
// DBG::out($select);exit;
    return $select;
  }

  /**
   * Get a selection of flashcards for review, as an array of flashcard ids.
   * 
   * Orders flashcards by expiredate (longest expired first), and then
   * randomize cards that expire on the same date.
   * 
   * Only works for current user (sqlLocalTime).
   * 
   * @param  mixed    $box    'all' or a Leitner box number starting from 1
   * @param  string   $type   'expired', 'untested', 'relearned', 'known', anything else means 'fresh' (non-expired)
   * @param  string   $filt   'rtk1', 'rkt3', '' for all kanji
   * @param  boolean  $merge  True to merge flashcards from given box with higher boxes
   * 
   * @return array    Flashcard ids (normalized as int).
   */
  public static function getFlashcardsForReview($box, $type, $filt, $merge = false)
  {
    $user = sfContext::getInstance()->getUser();
    $userId = $user->getUserId();
    $sqlLocalTime = new coreDbExpr(UsersPeer::sqlLocalTime());

    if ($type === 'relearned')
    {
      // select cards from relearned kanji selection
      $select = LearnedKanjiPeer::getInstance()->select('ucs_id');
      $select->where('userid = ?', $userId);
    }
    else
    {
      $select = self::getInstance()->select('ucs_id');
      $select = self::filterByUserId($select, $userId);

      if ($filt !== '') {
        $select = self::filterByRtk($select, $filt);
      }
    }

    switch ($type)
    {
      case 'untested':
        $order_by = 'expiredate';

        if ($user->getUserSetting('OPT_NO_SHUFFLE')) {
          // do not shuffle new cards, order by sequence number
          $select->columns(['seq_nr' => rtkIndex::getSqlCol()]);
          $select = KanjisPeer::joinLeftUsingUCS($select);
          $order_by = $order_by . ', ' . rtkIndex::getSqlCol() . ' ASC';
        }
        else {
          $order_by = $order_by . ', RAND()';
        }
        $select->where('totalreviews = 0');
        $select->order($order_by);
        break;
        
      case 'relearned':
        $select->order('RAND()');
        break;

      default:
        if ($type == 'known') {
          // known cards are all cards reviewed once and not currently in the failed stack
          // $box should be 'all' (cf. below)
        }
        elseif ($type == 'expired') {
          // expired cards (orange stacks, due for review)
          $select->where('totalreviews > 0 AND expiredate <= ?', $sqlLocalTime);
        }
        else {
          // fresh cards (green stacks, not due for review)
          $select->where('totalreviews > 0 AND expiredate > ?', $sqlLocalTime);
        }
        
        if ($box == 'all') {
          $select->where('leitnerbox > 1');
        }
        elseif ($merge) {
          $select->where('leitnerbox >= ?', $box);
        }
        else {
          $select->where('leitnerbox = ?', $box);
        }

        // "known" cards (free review mode) don't care about expiry date
        $select->order($type == 'known' ? 'RAND()' : 'expiredate, RAND()');
        break;
    }

// DBG::out($select);exit;

    $db = self::getInstance()->getDb();
    $ids = $db->fetchCol($select);

    return array_map('intval', $ids);
  }

  /**
   * Return array of cards with the day diff for each due card over the next N
   * days.
   *
   * @param   int     $userId
   *
   * @return  array   Array of day diffs (1 column), or empty array
   */
  public static function getDueCardsByDay()
  {
    $user = sfContext::getInstance()->getUser();
    $exprLocalTime = UsersPeer::sqlLocalTime();
    $exprDayDiff   = 'DATEDIFF(expiredate, '.$exprLocalTime.')';

    $select = self::getInstance()->select(new coreDbExpr($exprDayDiff))
      ->where('userid = ?', $user->getUserId())
      ->where('leitnerbox > 1') // not failed or new cards
      ->where('expiredate < DATE_ADD('.$exprLocalTime.', INTERVAL '.DueCardsGraphComponent::GRAPH_DAYS.' DAY)');

//echo $select;exit;
   return self::getInstance()->getDb()->fetchCol($select);
  }

  /**
   * Returns Select object for the Review Summary page.
   *
   * Select all flashcards reviewed since the given (localized) timestamp.
   * 
   * @see    getLocalizedTimestamp()
   * 
   * @param  int    $userId  
   *
   * @return coreDatabaseSelect
   */
  public static function getReviewSummaryListSelect($userId, $ts_start)
  {
    $select = self::getInstance()->select([
      'reviews.ucs_id',
      'seq_nr' => rtkIndex::getSqlCol(),
      'failurecount',
      'successcount',
      'leitnerbox',
      'ts_lastreview' => 'UNIX_TIMESTAMP(lastreview)',
      'kanji',
      'onyomi',
      'strokecount'
    ]);
    $select->where('UNIX_TIMESTAMP(lastreview) >= ?', $ts_start);
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select = self::filterByUserId($select, $userId);
    $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
//DBG::out($select);exit;
    return $select;
  }

 /**
   * Returns the last kanji card index in an uninterrupted sequence
   * starting at 1 -- any cards after a gap are ignored.
   *
   * @return int  Sequence number of last kanji in sequence (starts at 1), or 0
   */
  public static function getSequencePosition($userId)
  {
    $select = self::getInstance()->select();
    $select = self::filterByRtk($select, 'rtk1');
    $select = self::filterByUserId($select, $userId);
    $select->columns(['idx' => rtkIndex::getSqlCol()]);

    $indices = self::getInstance()->getDb()->fetchCol($select);
    $count = count($indices);

    if (!$count) {
      return 0;
    } 

    $indices = array_map('intval', $indices);
    sort($indices);

    // find where the first gap of the sequence is
    $diff = array_diff(range(1, $count), $indices);

    // if there is no gap in sequence then position = number of flashcards
    $position = count($diff) ? min($diff) - 1 : $count;

// LOG::info($indices);
// LOG::info('pos', $position);
   
    return $position;
  }
  
  /**
   * Returns progress status for Check Progress page.
   *
   * All flashcards not in the current sequence will be in lesson 0.
   * 
   * Returns:
   *  array( <lessNr> => array( <lessonId>, <total>, <pass>, <fail> ), ... )
   *     
   * @return  array  Array of objects
   */
  public static function getProgressChartData($userId)
  {
    $select = self::getInstance()->select(['ucs_id', 'seq_nr' => rtkIndex::getSqlCol(), 'leitnerbox', 'totalreviews', 'failurecount', 'successcount']);
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select = self::filterByUserId($select, $userId);
    $select->query();

    $lessons = [];
    
    $db = self::getInstance()->getDb();

    while ($row = $db->fetchObject())
    {
      $lessNr = rtkIndex::getLessonForIndex((int)$row->seq_nr);

      if (!isset($lessons[$lessNr])) {
        $lessons[$lessNr] = (object) ['lessonId' => $lessNr, 'total' => 0, 'pass' => 0, 'fail' => 0];
      }

      // ref
      $lesson =& $lessons[$lessNr];

      $lesson->total++;
      
      if ($row->leitnerbox > 1 && $row->totalreviews > 0) {
        $lesson->pass++;
      }
      elseif ($row->leitnerbox == 1 && $row->totalreviews > 0) {
        $lesson->fail++;
      }
    }
      
/*
    $select = self::getInstance()->select(array(
        'lessonId'  => 'lessonnum',
        'total'     => 'COUNT(*)',
        'pass'      => 'SUM(leitnerbox > 1 AND totalreviews > 0)',
        'fail'      => 'SUM(leitnerbox = 1 AND totalreviews > 0)'
      ));
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select->group('lessonnum ASC');
*/

//DBG::printr($lessons);exit;

    return $lessons;
  }

  /**
   * Return count of failed kanji for user.
   * 
   * @param
   * @return
   */
  public static function getRestudyKanjiCount($userId)
  {
    return self::getInstance()->count('userid = ? AND leitnerbox=1 AND totalreviews>0', $userId);
  }

  /**
   * Return select object for the Restudy Kanji list.
   * 
   * @param  int   $userId
   * 
   * @return coreDatabaseSelect
   */
  public static function getRestudyKanjiListSelect($userId)
  {
    $select = self::getInstance()
      ->select([
        'kanji',
        'seq_nr' => rtkIndex::getSqlCol(),
        'successcount',
        'failurecount',
        'ts_lastreview' => 'UNIX_TIMESTAMP(lastreview)',
        'is_learned' => new coreDbExpr('(lk.ucs_id IS NOT NULL)')
      ]);

    $select->joinLeftUsing(LearnedKanjiPeer::getInstance()->getName().' lk', 'userid,ucs_id');

    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
    $select->where('leitnerbox=1 AND totalreviews>0');
    $select = self::filterByUserId($select, $userId);

    return $select;
  }

  /**
   * Return data for Restudy List "quick" view in the side column of the Study page.
   * 
   * Returns all failed kanji that are not currently in the learned list.
   * 
   * @param
   * 
   * @return array<array>  Resultset
  public static function getRestudyQuickList($userId)
  {
    $select = self::getInstance()->select(array('seq_nr' => rtkIndex::getSqlCol()));
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select->joinLeftUsing('learnedkanji', 'userid, ucs_id');
    $select = self::filterByUserId($select, $userId);
    $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);

    $select->where('leitnerbox=1 AND totalreviews>0')
      ->where('learnedkanji.ucs_id IS NULL')  // not in the learned list
      ->order('seq_nr')
      ->limit(10);

    return self::getInstance()->getDb()->fetchAll($select);
  }
  */

  /**
   * Returns the first kanji in index order,
   * which is in the failed stack and not yet "learned".
   * 
   * @return mixed  UCS-2 code value (int) of the next restudy kanji, or false
   */
  public static function getNextUnlearnedKanji($userId)
  {
    $tableName = self::getInstance()->getName();
    $select = self::getInstance()->select($tableName.'.ucs_id');
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select->joinLeftUsing('learnedkanji', 'userid, ucs_id');
    $select = self::filterByUserId($select, $userId);
    
    $select->where('leitnerbox=1 AND totalreviews>0')
      ->where('learnedkanji.ucs_id IS NULL')  /* not in the learned list */
      ->order(rtkIndex::getSqlCol().' ASC')
      ->limit(1);

    $ucsId = self::getInstance()->getDb()->fetchOne($select);

    return $ucsId !== false ? intval($ucsId) : false;
  }

  /**
   * FlashcardReview callback for the review page.
   * 
   * Note: must sanitize data!
   * 
   * Flashcard answer data is set by the front end code (review.js):
   * 
   *    id     Flashcard id = UCS-2 code value
   *    r      Answer (cf. FlashcardReview.php const)
   *    
   * @param  int      $ucsId     Flashcard id (UCS-2 code value)
   * @param  object   $oData  Flashcard answer data
   *
   * @return boolean   True if update/skip/delete went succesfully
   */
  public static function putFlashcardData($ucsId, $oData)
  {
    assert($ucsId > 0);
    assert(isset($oData->r));
//DBG::printr($oData);

    $userId = sfContext::getInstance()->getUser()->getUserId();

    if ($oData->r === LeitnerSRS::RATE_SKIP)
    {
      // skip flashcard : just ignore it (pretend it's been handled)
      $result = true;
    }
    elseif ($oData->r === LeitnerSRS::RATE_DELETE)
    {
      $result = self::deleteFlashcards($userId, [$ucsId]) > 0;
      
      sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent(null, 'flashcards.update'));
    }
    else
    {
      $curData = self::getFlashcardData($userId, $ucsId);
  
      if (!$curData) {
        // if the card was somehow deleted, return true so the client can clear the card from sync buffer
        return true;
      }
// LOG::info("rate {$oData->r}", json_encode($curData));

      $update = LeitnerSRS::getInstance()->rateCard($curData, $oData->r);

      $result = $update && self::updateFlashcard($userId, $ucsId, $update);
    }

    // clear relearned kanji if successfull answer
    if ($result
        && ($oData->r === LeitnerSRS::RATE_HARD ||
            $oData->r === LeitnerSRS::RATE_YES  ||
            $oData->r === LeitnerSRS::RATE_NO   ||
            $oData->r === LeitnerSRS::RATE_EASY ||
            $oData->r === LeitnerSRS::RATE_DELETE))
    {
      LearnedKanjiPeer::clearKanji($userId, $ucsId);
    }

    return $result;
  }

  /**
   * Moves flashcard to the red pile (stack 1) by rating it as "No".
   * 
   * @param int   $userId 
   * @param int   $ucsId
   *
   * @return bool   Whether SQL update was successful
   */
  public static function failFlashcard($userId, $ucsId)
  {
    $cardData = self::getFlashcardData($userId, $ucsId);
    
    if ($cardData === false) return false;

    // rate card as "not remembered" (No)
    $update = LeitnerSRS::getInstance()->rateCard($cardData, LeitnerSRS::RATE_NO);

    return $update && self::updateFlashcard($userId, $ucsId, $update);
  }

  /**
   * Update flashcard row data, plus:
   * 
   *   - sets `lastreview` to user's local time
   *   - if `interval_days` is provided, converts it to `expiredate`
   * 
   * @param int $userId
   * @param int $ucsId
   * @param array $cardUpdate  Row data to update, +optional value "interval_days"
   * 
   * @return bool   Whether SQL update was successful
   */
  public static function updateFlashcard(int $userId, int $ucsId, array $cardUpdate)
  {
    $sqlLocalTime = UsersPeer::sqlLocalTime();

    // update "due" timestamp if interval is provided (cf. LeitnerSRS::rateCard())
    if (isset($cardUpdate['interval_days']))
    {
      $interval_days = $cardUpdate['interval_days'];
      unset($cardUpdate['interval_days']);

      $user = sfContext::getInstance()->getUser();
      $sqlExpireDate = sprintf('DATE_ADD(%s, INTERVAL %d DAY)', $sqlLocalTime, $interval_days);

      $cardUpdate['expiredate'] = new coreDbExpr($sqlExpireDate);
    }

    // always update last review timestamp
    $cardUpdate['lastreview'] = new coreDbExpr($sqlLocalTime);

// LOG::info("updateFlashcard() ", $cardUpdate);

    // foolproof check : leitnerbox should NEVER be zero or NULL
    if (array_key_exists('leitnerbox', $cardUpdate)
      && (
        !ctype_digit((string)$cardUpdate['leitnerbox'])
        || $cardUpdate['leitnerbox'] <= 0
      )
    ) {
      error_log("updateFlashcard() INVALID! ".json_encode($cardUpdate));
      return false;
    }

    return self::getInstance()->update($cardUpdate, 'userid = ? AND ucs_id = ?', [$userId, $ucsId]);
  }

  /**
   * Add a set of new flashcards to the user's deck.
   * 
   * Note: remove duplicate cards first with filterExistingCards() !
   * 
   * @param   int     $userId
   * @param   array   $cardSel   Array of UCS-2 codes
   * 
   * @return array  Array of successfully added flashcard ids
   */
  static public function addSelection($userId, array $cardSel)
  {
    // create new flashcards
    $cards = ReviewsPeer::addFlashcards($userId, $cardSel);
    if (count($cards))
    {
      ActiveMembersPeer::updateFlashcardCount($userId);

      sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent(null, 'flashcards.update'));

    }
    return $cards;
  }

  /**
   * Delete all given flashards, returns an array of ids of flashcards
   * that were succesfully deleted.
   * 
   * @param   int     $userId
   * @param   array   $cardSel   Array of flashcard ids (UCS-2)
   * 
   * @return array  Array of successfully deleted flashcards (ids) or false
   */
  static public function deleteSelection($userId, array $cardSel)
  {
    $cards = ReviewsPeer::deleteFlashcards($userId, $cardSel);
    if (is_array($cards) && count($cards))
    {
      ActiveMembersPeer::updateFlashcardCount($userId);

      sfContext::getInstance()->getEventDispatcher()->notify(new sfEvent(null, 'flashcards.update'));
    }
    return $cards;
  }

  /**
   * Filter out flashcard ids in the selection that are already present in the
   * user's deck. Returns an array with only the flashcard ids that don't
   * exist in the user's deck.
   * 
   * @param   int     $userId
   * @param   array   $cards       Array of UCS-2 codes
   *
   * @return  array   Array of UCS-2 codes
   */
  static public function filterExistingCards($userId, array $cards)
  {
    $select = self::getInstance()->select('ucs_id');
    $select = self::filterByUserId($select, $userId);

    // array of flashcard ids that are in the user's deck
    $userCards = self::getInstance()->getDb()->fetchCol($select);

    // filter out ids that are in both sets
    $newCards = array_diff($cards, $userCards);

    return $newCards;
  }

  /**
   * Add a set of flashcards all at once.
   *
   * Duplicate entries should be filtered out before calling this method!
   * 
   * @param  int    $userId
   * @param  array  $cards    Array of UCS-2 codes
   * 
   * @return array  Returns an array with ids of succesfully created flashcards
   */
  private static function addFlashcards($userId, array $cards)
  {
    $db = self::getInstance()->getDb();
    $tableName = self::getInstance()->getName();
    
    // only lock if necessary
    $lockTable = count($cards) > 20;

    // lock the table (to speedup index) (minimal speed gain..)
    if ($lockTable) {
      $db->query('LOCK TABLE '.$tableName.' WRITE');
    }

    // prepare statement and execute for all cards
    $stmt = new coreDatabaseStatementMySQL($db,
      sprintf('INSERT %s (userid,ucs_id,created_on,leitnerbox) VALUES (%d,?,NOW(),1)', $tableName, $userId));

    try
    {
      $done = [];
      foreach ($cards as $id)
      {
        if (!$stmt->execute([$id]))
        {
          break;
        }
        $done[] = $id;
      }
    }
    catch (sfException $e)
    {
    }

    // unlock table
    if ($lockTable) {
      $db->query('UNLOCK TABLES');
    }

    // return succesfully added ids
    return $done;
  }

  /**
   * Delete a set of flashcards.
   *
   * @param  int    $userId
   * @param  array  $cards    Array of flashcard ids (UCS-2)
   * 
   * @return array  Returns an array of succesfully deleted flashcard ids or false
   */
  private static function deleteFlashcards($userId, $cards)
  {
    $db = self::getInstance()->getDb();
    $tableName = self::getInstance()->getName();
  
    // only lock if necessary
    $lockTable = count($cards) > 20;

    // lock the table (to speedup index) (minimal speed gain..)
    if ($lockTable) {
      $db->query('LOCK TABLE '.$tableName.' WRITE');
    }

    // prepare statement and execute for all cards
    $stmt = new coreDatabaseStatementMySQL($db,
      sprintf('DELETE FROM %s WHERE userid = %d AND ucs_id = ?', $tableName, $userId));

    try
    {
      $done = [];
      foreach ($cards as $id)
      {
        if (!$stmt->execute([$id]))
        {
          break;
        }
        if ($stmt->rowCount() > 0) {
          $done[] = $id;
        }
      }
    }
    catch (sfException $e)
    {
      $done = false;
    }

    // unlock table
    if ($lockTable) {
      $db->query('UNLOCK TABLES');
    }

    // return succesfully added ids
    return $done;
  }

   /**
   * Filter flashcard selection by frame number for given RtK Volume
   * (or no filter = all).
   * 
   * @param  coreDatabaseSelect   $select
   * @param  string   $filter     'rtk1', 'rtk3', 'rtk1+3', '' (no filter)
   * 
   * @return coreDatabaseSelect   Returns modified select object
   */
  private static function filterByRtk($select, $filter = '')
  {
    // always add Sequences join, will be discarded by SQL if not used anyway
    $curSeq = rtkIndex::inst();

    if ($filter !== '')
    {
      $select = KanjisPeer::joinLeftUsingUCS($select);
      $idxCol = rtkIndex::getSqlCol();

      switch ($filter)
      {
        case 'rtk1':
          $select->where($idxCol.' <= ?', $curSeq->getNumCharactersVol1());
          break;
        case 'rtk3':
          $select->where($idxCol.' > ? AND '.$idxCol.' <= ?', [$curSeq->getNumCharactersVol1(), $curSeq->getNumCharactersVol3()]);
          break;
        case 'rtk1+3':
          $select->where($idxCol.' <= ?', $curSeq->getNumCharacters());
          break;
        default:
          break;
      }
    }

    return $select;
  }

 /**
   * Apply user id filter to select object.
   * 
   * @param  coreDatabaseSelect  $select
   * @param  int   $userId
   * 
   * @return coreDatabaseSelect
   */
  private static function filterByUserId(coreDatabaseSelect $select, $userId)
  {
    return $select->where(self::getInstance()->getName().'.userid = ?', $userId);
  }
}
