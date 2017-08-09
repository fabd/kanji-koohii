<?php
/**
 * Stories Peer.
 *
 * Methods:
 *   getStory($userId, $ucsId)
 *   getStoryId($userId, $ucsId)
 *   updateStory($userId, $ucsId, $data)
 *   deleteStory($userId, $ucsId)
 *   getFormattedStory($story, $keyword, $bSubstituteLinks = true, $linebreaks = true)
 *   getFormattedKanjiLink($matches)
 *   getSharedStories($ucsId, $keyword, $userId)
 *   getStoriesCounts($userId)
 *   getMyStoriesSelect($userId)
 *   getSelectForExport($userId)
 *
 * 
 * @author  Fabrice Denis
 */

class StoriesPeer extends coreDatabaseTable
{
  protected
    $tableName = 'stories',
    $columns = array('updated_on'); // timestamp columns must be declared for insert/update/replace

  /**
   * This function must be copied in each peer class.
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   *
   * @return int|boolean   Number of rows, or FALSE
   */
  public static function getNumStories($userId)
  {
    return self::getInstance()->count('userid = ?', $userId);
  }

  /**
   * Returns story and story settings for given user.
   * 
   * @return object  Stories row data as object, or false.
   */
  public static function getStory($userId, $ucsId)
  {
    assert('(int)$ucsId >= 0x3000');

    $select = self::getInstance()->select()
      ->where('userid = ? AND ucs_id = ?', array($userId, $ucsId))
      ->query();
    return self::$db->fetchObject();
  }

  /**
   * Return unique identifier for story
   *
   * @return  int    story id (sid), or false (no result)
   */
  public static function getStoryId($userId, $ucsId)
  {
    $select = self::getInstance()->select('sid')->where('userid = ? AND ucs_id = ?', array($userId, $ucsId));
    $sid = self::$db->fetchOne($select);
    return (false === $sid) ? false : (int)$sid;
  }

  /**
   * Returns ONE starred story for the user, and given character (no order is
   * guaranteed).
   *
   * @return object  Stories row data as object, or false.
   */
  public static function getFavouriteStory($userId, $ucsId)
  {
    $authorId = StoryVotesPeer::getStarredStory($userId, $ucsId);

    return false !== $authorId ? StoriesPeer::getStory($authorId, $ucsId) : false;
  }

  /**
   * Create/Update story and story settings for user.
   * 
   * @param  int    $userId    User id
   * @param  int    $ucsId     UCS-2 code value.
   * @param  array  $data      Cols: text, public
   *
   * @return  bool    true if the story update (stories table) is succesful
   */
  public static function updateStory($userId, $ucsId, $data)
  {
    $result = true;

    assert('(int)$ucsId >= 0x3000');

    $storyId = self::$db->fetchOne(
      self::$db->select('sid')->from('stories')->where('userid = ? AND ucs_id = ?', array($userId, $ucsId))
    );

    // either false (no row), or a positive auto increment number > 0 
    assert('$storyId !== 0');

    if (false === $storyId)
    {
      $data = array_merge($data, array('userid' => $userId, 'ucs_id' => $ucsId));
      self::getInstance()->insert($data);
      $storyId = self::$db->lastInsertId();
    }
    else
    {
      $result = $result && self::getInstance()->update($data, 'sid = ?', $storyId);
    }

    // after insert or update we need a valid story id > 0
    assert('$storyId > 0', 'updateStory()');

    // if this table isn't updated it's not crucial (but the other way around is)
    StoriesSharedPeer::updateStoryRef($storyId, $ucsId, $userId, $data['public'] === 1);

    return $result;
  }
  
  /**
   * Delete a story.
   * 
   * @param  int    $userId   User id
   * @param  int    $ucsId    UCS-2 character code.
   *
   * @return
   */
  public static function deleteStory($userId, $ucsId)
  {
    assert('(int)$ucsId >= 0x3000');

    if (false !== ($storyId = self::getStoryId($userId, $ucsId)))
    {
      self::getInstance()->delete('sid = ?', $storyId);
      StoriesSharedPeer::deleteStoryRef($storyId);
    }
  }

  /**
   * Return a story formatted for display, with automatic keyword bolding.
   * 
   * The input story is ESCAPED before html tags are inserted for the formatting.
   * It is assumed strip_tags() was used previously. The returned string should not be escaped
   * again in the view template.
   * 
   * @param  String   $story
   * @param  String   $keyword
   * @param  Boolean  $bSubstituteLinks    True to show frame number references as links otherwise plain text.
   * @param  Boolean  $linebreaks          Replace CR/LF with BR tags.
   *
   * @return String
   */
  public static function getFormattedStory($story, $keyword, $bSubstituteLinks = true, $linebreaks = true)
  {
    // Links helper is used by getFormattedKanjiLink() call
    sfProjectConfiguration::getActive()->loadHelpers(array('Tag', 'Url'));

    $s = $story;

    // minimal punctuation : upper case first word (ignore non-alphabet & multibyte)
    if (preg_match('/[a-z]/i', $s, $match, PREG_OFFSET_CAPTURE))
    {
      $i = $match[0][1];
      $s = substr($s, 0, $i) . ucfirst(substr($s, $i));
    }

    // minimal punctuation : end sentence with dot (accepts "quote." spelling)
    if (preg_match ('/[^.!?][^.!?]$/', $s))
    {
      $s = $s . '.';
    }

    // format mnemonic keyword if keyword is found within text
    $keywords = explode(rtkIndex::EDITION_SEPARATOR, $keyword);
    if (count($keywords) > 1)
    {
      // use 4th edition keyword if multiple edition keyword
      $keyword = $keywords[1];
    }
    
    // remove trailing '?' or '...'
    $keyword = preg_replace('/\s*\.\.\.$|\s*\?$/', '', $keyword);
    // fixes highlighting keywords like "lead (metal)" or "abyss [old]"
    if (strstr($keyword,'(')) { $keyword = preg_replace('/\s+\([^\)]+\)/', '', $keyword); }
    if (strstr($keyword,'[')) { $keyword = preg_replace('/\s+\[[^\]]+\]/', '', $keyword); }

    if (strlen($keyword)==1)
    {
      $keyword = $keyword . '($|\s+)';
    }

    // escape text before adding html tags, replace the single quotes with another
    // special character because the escaping uses htmlspecialchars() inserts &#039;
    // and then the '#' character is matched by another regexp as the #keyword# marker
    $s = str_replace('\'', '`', $s);
    $s = htmlspecialchars($s, ENT_QUOTES, sfConfig::get('sf_charset'));

    // line breaks
    if ($linebreaks)
    {
      $s = str_replace(array("\r\n", "\n", "\r"), '<br/>', $s);
    }

    // remove extra spaces
    $s = preg_replace('/\s\s+/u', ' ', $s);

    $s = preg_replace('/(^|\s+)('.preg_quote($keyword).')/i', '<strong>$1$2</strong>', $s);

    // format mnemonic #keyword#
    $s = preg_replace('/#([^#]+)#/ui', '<strong>$1</strong>', $s);
    // format mnemonic *primitives*
    $s = preg_replace('/\*([^\*]+)\*/ui', '<em>$1</em>', $s);

    // substitute {...} references to other characters
    if ($bSubstituteLinks)
    {
      // parse for {...} references, do not assume kanji as old stories use frame numbers
      $s = preg_replace_callback('/{([^}]+)}/u', array('StoriesPeer', 'getFormattedKanjiLink'), $s);
    }
    else
    {
      $s = preg_replace_callback(
        '/{([0-9]+)}/',
        create_function(
          // single quotes are essential here, or alternative escape all $ as \$
          '$matches',
          'return sprintf("#%d", $matches[1]);'
        ), $s);
    }

    // Now restore the single quotes (as escaped single quotes)
    $s = str_replace('`', '&#039;', $s);

    return $s;
  }

  /**
   * Returns a formatted character reference for {n} syntax used in stories
   * where n can be an extended frame number (Heisig ... UCS-2), or a utf8
   * character.
   *
   * If the character exists in the user's selected index, the frame number is
   * also displayed (relative to this user's chosen index).
   * 
   * @param  array    $matches  Reg exp matches, $matches[1] is the kanji id
   * 
   * @return string
   */
  public static function getFormattedKanjiLink($matches)
  {
    $id = $matches[1];

    // convert index references in old stories to the index-independent character
    if (true === ctype_digit($id))
    {
      StoriesPeer::useOldStoriesFix();
      $id = rtxIndexOldStoriesFix::getCharForOldIndex((int)$id);
    }

    $link = link_to($id, 'study/edit?id='.$id);

    $frameNr = rtkIndex::getIndexForChar($id);
    if (false !== $frameNr)
    {
      $link .= ' (<span class="frnr">#'.$frameNr.'</span>)';
    }

    /* old code
    $keyword = '#'.$id;
    $link = link_to($keyword, 'study/edit?id='.$id);*/

    return $link;
  }

  public static function useOldStoriesFix()
  {
    require_once(sfConfig::get('sf_app_lib_dir').'/model/'.CJ_MODE.'IndexOldStoriesFix.php');
  }

  /**
   * Returns array of stories for the "Favourite" and "Newest" sections in the
   * Study page.
   *  
   *
   * @param int    $ucsId  UCS-2 code value.
   *
   * @return  array
   */
  public static function getSharedStories($ucsId, $keyword, $userId, $type)
  {
    assert('is_int($ucsId) && $ucsId >= 0x3000');

    $select = self::$db->select();

    $select->columns(array(
        'u.username','lastmodified' => 'DATE_FORMAT(ss.updated_on,\'%e-%c-%Y\')',
        's.text', 'ss.stars', 'kicks' => 'ss.reports'
      ));

    if ($type === 'starred')
    {
      // fav stories
      $select
        ->columns('sv.authorid')
        ->from('storyvotes sv')
        ->joinLeft('stories s', 'sv.authorid = s.userid AND sv.ucs_id = s.ucs_id') /* TODO sid */
        ->joinLeft('stories_shared ss', 'ss.sid = s.sid')
        ->joinLeft('users u', 'u.userid = sv.authorid')
        ->where('sv.userid = ? AND sv.ucs_id = ? AND sv.vote = 1', array($userId, $ucsId));
        /* disable to avoid temporary ->order('ss.stars DESC');*/
    }
    elseif ($type === 'newest')
    {
      // newest stories
      $select
        ->columns(array('authorid' => 'ss.userid'))
        ->from('stories_shared ss')
        ->joinLeft('stories s', 'ss.sid = s.sid')
        ->joinLeft('users u', 'u.userid = ss.userid')
        ->where('ss.ucs_id = ? AND ss.updated_on >= DATE_ADD(CURDATE(),INTERVAL -1 MONTH)', $ucsId)
        ->order('ss.stars DESC, ss.updated_on DESC');
    }
    else
    {
      throw new sfError404Exception('bug');
    }

    $select->limit(10);

//DBG::out($select);exit;

    // must fetch all here because getFormattedStory() will do more querries
    $fetchMode = self::$db->setFetchMode(coreDatabase::FETCH_OBJ);
    $rows = self::$db->fetchAll($select);
    self::$db->setFetchMode($fetchMode);

    $stories = array();

    foreach ($rows as $row)
    {
      // do not show 0's
      if (!$row->stars) { $row->stars = ''; }
      if (!$row->kicks) { $row->kicks = ''; }

      $row->text   = StoriesPeer::getFormattedStory($row->text, $keyword, true, false);

      $stories[] = $row;
    }
    
    return $stories;
  }

  /**
   * Returns count of shared and private stories for given user.
   * 
   * @param  int  $userId   User's id.
   * @return object          Object with properties 'private' 'public' and 'total'
   */
  public static function getStoriesCounts($userId)
  {
    $num_stories = new stdClass;
    $num_stories->private = 0;
    $num_stories->public  = 0;
    
    self::getInstance()->select(array('public', 'count' => 'COUNT(*)'))
      ->where('userid = ?', $userId)
      ->group('public')
      ->query();
    while ($R = self::$db->fetchObject())
    {
      if ($R->public==0){
        $num_stories->private = $R->count;
      }
      else {
        $num_stories->public = $R->count;
      }
    }
    $num_stories->total = $num_stories->private + $num_stories->public;

    return $num_stories;
  }

  /**
   * Returns Select object for My Stories component.
   * 
   * @param   int     $userId
   *
   * @return
   */
  public static function getMyStoriesSelect($userId)
  {
    $select = self::$db->select()->columns(
      array(
        'seq_nr' => rtkIndex::getSqlCol(), 'kanji', 'story' => 'text', 'public',
        'stars', 'kicks' => 'reports', 's.updated_on', 'ts_dispdate' => 'UNIX_TIMESTAMP(s.updated_on)'
      ));
    $select->from('stories s')
           ->joinLeft('stories_shared ss', 'ss.sid = s.sid')
           ->joinLeft('kanjis', 'kanjis.ucs_id = s.ucs_id');
//   $select->joinLeft(StoriesSharedPeer::getInstance()->getName(), 'stories.sid = stories_shared.sid');
//   $select = KanjisPeer::joinLeftUsingUCS($select);
    $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
    $select->where('s.userid = ?', $userId);

    return $select;
  }

  /**
   * Returns select for export to CSV.
   * 
   * @return coreDatabaseSelect
   */
  public static function getSelectForExport($userId)
  {
    $select = self::getInstance()->select(array(
    // Order is important! See ExportCSV settings in action.
      'framenr' => rtkIndex::getSqlCol(),
      'kanji',
      'keyword' => CustkeywordsPeer::coalesceExpr(),
      'public',
      'last_edited' => 'stories.updated_on',
      'story' => 'text'))
      ->where('stories.userid = ?', $userId)
      ->order('framenr', 'ASC');
    $select = KanjisPeer::joinLeftUsingUCS($select);
    $select = CustkeywordsPeer::addCustomKeywordJoin($select, $userId);
//DBG::out($select);exit;
    return $select;
  }
}
