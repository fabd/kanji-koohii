<?php
/**
 * Custom Keywords table.
 * 
 * @author  Fabrice Denis
 */

class CustkeywordsPeer extends coreDatabaseTable
{
  protected $tableName = 'custkeywords';

  // timestamp cols for self::insert/update/replace
  protected $columns = ['created_on', 'updated_on'];

  /**
   * This function must be copied in each peer class.
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Return custom keyword if edited by user, or null.
   * 
   * @param  int   $userid 
   * @param  int   $ucsId    UCS-2 code value.
   *
   * @return ?string
   */
  public static function getCustomKeyword($userId, $ucsId): ?string
  {
    $select = self::getInstance()->select('keyword')->where('userid = ? AND ucs_id = ?', [$userId, $ucsId]);
//DBG::out($select);
    $keyword = self::$db->fetchOne($select);
    return (false !== $keyword) ? $keyword : null;
  }

  /**
   * Returns coalesced (customkeyword, default keyword) for given user and
   * character.
   *
   * @param  int     $userId
   * @param  int     $ucsId    UCS-2 code value.
   *
   * @return mixed   Keyword (string) or null if not a Heisig character.
   */
  public static function getCoalescedKeyword($userId, $ucsId)
  {
    $select = self::$db->select()->from(KanjisPeer::getInstance()->getName());
    $select = self::addCustomKeywordJoin($select, $userId);
    $select->where('kanjis.ucs_id = ?', $ucsId);
    $keyword = self::$db->fetchOne($select);
    return (false !== $keyword) ? $keyword : null;
  }

  /**
   * Updates custom keyword.
   * 
   * @param  int    $userId 
   * @param  int    $ucsId    UCS-2 code value.
   * @param  string $keyword  Custom keyword string (must be sanitized!)
   *
   * @return boolean  Returns true if succesfull.
   */
  public static function updateCustomKeyword($userId, $ucsId, $keyword)
  {
    $data  = ['keyword' => $keyword];
    return self::getInstance()->replace($data, ['userid' => $userId, 'ucs_id' => $ucsId]);
  }

  /**
   * Delete
   * 
   * @param  int    $userId 
   * @param  int    $ucsId    UCS-2 code value.
   *
   * @return boolean  Returns true if succesfull.
   */
  public static function deleteCustomKeyword($userId, $ucsId)
  {
    return self::getInstance()->delete('userid = ? AND ucs_id = ?', [$userId, $ucsId]);
  }

  /**
   * Import a selection of custom keywords.
   * 
   * @param  int   $userId 
   * @param  array $keywords   Assoc array (ucs_id => keyword)
   * @param  coreRequest  $request    Request object to set errors
   *
   * @return bool  True on success, false if any error occurs.
   */
  public static function importList($userId, array $keywords, $request)
  {
    $tableName = self::getInstance()->getName();
    $db = self::$db;

    // obtenir l'id des custom keywords existants, comme clé dans un array assoc.
    $colUcs = $db->fetchCol(self::getInstance()->select('ucs_id')->where('userid = ?', $userId));
    $colUcs = array_flip(array_map('intval', $colUcs));

    // ~160ms for 2043 keywords
    //sfProjectConfiguration::getActive()->profileStart();

    // lock the table (to speedup index) (minimal speed gain..)
    $db->query("LOCK TABLE $tableName WRITE");

    try
    {
      // we must set created_on 
      $updateStmt = new coreDatabaseStatementMySQL($db, "UPDATE $tableName SET keyword = ? WHERE userid = $userId AND ucs_id = ?");
      $insertStmt = new coreDatabaseStatementMySQL($db, "INSERT $tableName (userid, ucs_id, keyword, created_on, updated_on) VALUES ($userId,?,?,NOW(),NOW())");

      foreach ($keywords as $ucsId => $keyword)
      {
        // if user already has custom keyword, do an UPDATE...
        if (isset($colUcs[$ucsId]))
        {
          if (!$updateStmt->execute([$keyword, $ucsId]))
          {
            $request->setError('x', 'Update error on "'.$keyword.'"');
            return false;
          }
        }
        else
        {
          if (!$insertStmt->execute([$ucsId, $keyword]))
          {
            $request->setError('x', 'Database insert error on "'.$keyword.'"');
            return false;
          }
        }
      }
    }
    catch (sfException $e)
    {
      $request->setError('x', 'Database error.');
      return false;
    }

    // unlock table
    self::$db->query('UNLOCK TABLES');

    //$t=sfProjectConfiguration::getActive()->profileEnd();
    //DBG::out("time $t  done: $done");exit;

    return true;
  }

  /**
   * Returns kanji data for given user, replacing the default keyword with the
   * user's customized keywords.
   *
   * Each returned row contains: 'framenum', 'kanji', 'keyword'.
   * 
   * @param  int  $userid
   *
   * @return array  Hash of ... framenum => array(   )
   */
  /*public static function getExportKeywords($userid)
  {
    $select = self::$db->select(array('seq_nr' => rtkIndex::getSqlCol(), 'kanji'));

    // get ALL customized keywords of this user, regardless of the characters
    $select->from(KanjisPeer::getInstance()->getName());
    $select = self::addCustomKeywordJoin($select, $userid);
    $select->query();
//DBG::out($select);exit;

    $keywords = array();
    while (false !== ($row = self::$db->fetch()))
    {
      $key = (string)$row['seq_nr'];
      $keywords[$key] = $row;
    }
//DBG::printr($keywords);exit;
    return $keywords;
  }*/

  /**
   * Get user's customized keywords coalesced in an assoc. array.
   *
   * FIXME  The non-limited query returns Heisig characters for now to avoid
   *        pulling 12559 rows (atm, only Heisig kanji can have cust. keyw).
   *
   * @param   int     $userId
   * @param   bool    $onlyFlashcards    true to limit to user's flashcards
   * 
   * @return array   Associative array:  ucs_id => (ucs_id, seq_nr, keyword)
   */
  public static function getCustomKeywords($userid, $onlyFlashcards = false)
  {
    $select = self::$db->select(['kanjis.ucs_id', 'seq_nr' => rtkIndex::getSqlCol()]);

    if (false === $onlyFlashcards) {
      // get all kanji keywords with custom keywords if defined
      $select->from(KanjisPeer::getInstance()->getName());
    }
    else {
      // get customized keywords only when there are corresponding flashcards
      $select->from(ReviewsPeer::getInstance()->getName());
      $select = KanjisPeer::joinLeftUsingUCS($select);
      $select->where('reviews.userid = ?', $userid);
    }

    // add last to avoid "ambiguous" ucs_id column
    $select = self::addCustomKeywordJoin($select, $userid);

    // FIXME? will need to change this if we want cust.kw for non-Heisig kanji
    $select->where(rtkIndex::getSqlCol() . ' < ?', rtkIndex::RTK_UCS);  

    // NOTE  Implicitly the result is limited to kanjis in the indexes (sequences!)
    $select->query();

    $keywords = [];
    while (false !== ($row = self::$db->fetch()))
    {
      $key = (string)$row['ucs_id'];
      $keywords[$key] = $row;
    }

    return $keywords;
  }

  /**
   * Returns COALESCE expression to get the customized keyword, or original.
   *
   *  => 'COALESCE(custkeywords.keyword, kanjis.keyword)'
   *
   * @return  string
   */
  public static function coalesceExpr()
  {
    return 'COALESCE('.self::getInstance()->getName().'.keyword, '.KanjisPeer::getInstance()->getName().'.keyword)';
  }

  /**
   * Adds the custom keyword join to a query (chainable).
   *
   * Assumes the query already includes the kanjis table and userid in the WHERE clause.
   *
   * @param  int  $userId  Match all cust keywords
   * 
   * @return object  coreDatabaseSelect
   */
  public static function addCustomKeywordJoin($select, $userId)
  {
    $custkeywords = self::getInstance()->getName();

    // add the custom keyword column to the query
    $select->columns(['keyword' => self::coalesceExpr()]);

    // use the userid from the joined table with USING clause
    //$select->joinLeftUsing($thisTableName, array('ucs_id', 'userid'));

    // here we want userid in the JOIN expression and not the WHERE clause, so that a COALESCE expression can be
    //  use to compare against any kanjis rows and not just custkeywords rows.
    $kanjis = KanjisPeer::getInstance()->getName();
    $expr   = "$kanjis.ucs_id = $custkeywords.ucs_id AND $custkeywords.userid = $userId";
    $select->joinLeft($custkeywords, $expr);

    return $select;
  }
}
