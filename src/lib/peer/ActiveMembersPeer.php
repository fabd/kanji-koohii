<?php
/**
 * ActiveMembers
 * 
 * The active members list uses this table to show statistics accross all users
 * in a single query.
 * 
 * This table can also store various information for a user to speed up querries,
 * but the information is temporary.
 *  
 * 
 * @author  Fabrice Denis
 */

class ActiveMembersPeer extends coreDatabaseTable
{
  const
    // shortcut for ::getInstance()->getName()
    TABLE = 'active_members';

  protected
    $tableName = 'active_members',
    $columns = [
      'userid',
      'fc_count',
      'last_review',
      'lastrs_start',
      'lastrs_pass',
      'lastrs_fail'
    ];

  /**
   * This function must be copied in each peer class.
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }
  
  /**
   * Return Select object for the active members list.
   * 
   * @param
   * @return coreDatabaseSelect
   */
  public static function getSelectForActiveMembersList()
  {
    return self::getInstance()
      ->select([self::getInstance()->getName().'.userid', 'username', 'location', 'fc_count', 'ts_lastreview' => 'UNIX_TIMESTAMP(last_review)'])
      ->joinUsing(UsersPeer::getInstance()->getName(), 'userid');
  }
  
  /**
   * Cleanup old data from table.
   * 
   * @param
   * @return
   */
  public static function deleteInactiveMembers()
  {
    return self::getInstance()->delete('last_review < ADDDATE(NOW(), INTERVAL -30 DAY)');
  }

  /**
   * Update the last flash card review timestamp and reviewed flash card count
   * to speedup the active members list table.
   * 
   * @param  int    $userId   User id.
   *    
   * @return boolean TRUE on success, FALSE on error.
   */
  public static function updateFlashcardInfo($userId)
  {
    $fc_count = ReviewsPeer::getFlashcardCount($userId);
    $lastreview_ts = ReviewsPeer::getMostRecentReviewTimeStamp($userId);

    $data = [
      'fc_count'   => $fc_count,
      'last_review' => $lastreview_ts
    ];

    return self::getInstance()->updateCols($userId, $data);
  }

  /**
   * Updates the flashcard count.
   * 
   * @param  int    $userId   User id.
   * 
   * @return boolean TRUE on success, FALSE on error.
   */
  public static function updateFlashcardCount($userId)
  {
    $data = [
      'fc_count' => ReviewsPeer::getFlashcardCount($userId)
    ];
    return self::getInstance()->updateCols($userId, $data);
  }

  /**
   * Save information about the last flashcard review session.
   * 
   * $data:
   * 
   *   ts_start  Timestamp begin of last flashcard review session
   *   fc_pass   Count of flashcards passed
   *   fc_fail   Count of flashcards failed
   * 
   * @param  int    $userId
   * @param  array  $data
   * 
   * @return
   */
  public static function saveReviewSummaryInfo($userId, $data)
  {
    $data = [
      'lastrs_start' => $data['ts_start'],
      'lastrs_pass'  => $data['fc_pass'],
      'lastrs_fail'  => $data['fc_fail']
    ];
    self::getInstance()->updateCols($userId, $data);
    return;
  }

  /**
   * Retrieve last review summary information, or false if not available.
   * 
   * @param  int    $userId
   * 
   * @return array  Parameters or FALSE
   */
  public static function getReviewSummaryInfo($userId)
  {
    self::getInstance()->select([
      'ts_start' => 'lastrs_start',
      'fc_pass'  => 'lastrs_pass',
      'fc_fail'  => 'lastrs_fail'])
      ->where('userid = ?', $userId)->query();
    $params = self::$db->fetch();
    
    return ($params!==false) && ($params['ts_start']>0) ? $params : false;
  }

  /**
   * Create or update information for active member.
   * 
   * Insert the user into the active users table if it is not present yet.
   * Since not all data may be updated at once, and a record may be
   * created with only part of the data, the default values come from
   * the database schema.
   * 
   * @param  int    $userId   User id.
   * @param  array  $coldata   Assoc.array of column data
   * 
   * @return boolean TRUE on success, FALSE on error.
   */
  protected function updateCols($userId, $coldata)
  {
    // create or update record
    if (self::getInstance()->count('userid = ?', $userId) <= 0)
    {
      $coldata['userid'] = $userId;
      return self::getInstance()->insert($coldata);
    }
    else
    {
      return self::getInstance()->update($coldata, 'userid = ?', $userId);
    }
  }
}
