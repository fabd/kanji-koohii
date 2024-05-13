<?php
/**
 * Users.
 * 
 * Methods:
 *  getUserBy($criteria, $value)
 *  getUser($username)
 *  getUserById($userid)
 *  getUserByEmail($email)
 *  getUserId($username)
 *  setLastlogin($userid, $timestamp = null)
 *  usernameExists($username)
 *  createUser(array $userinfo)
 *  deleteUser($userid)
 *  updateUser($userid, $columns)
 *
 *  sqlLocalTime($localTimezone = null)      Returns SQL statement for NOW() adjusted to user timezone
 *  intLocalTime()                           Returns ... same ... as an integer (returns the actual TIMESTAMP value)
 * 
 * 
 * @author  Fabrice Denis
 */

class UsersPeer extends coreDatabaseTable
{
  // shortcut for ::getInstance()->getName()
  const TABLE = 'users';

  protected $tableName = 'users';

  /**
   * Credential values as stored in `userlevel`.
   */
  const USERLEVEL_ADMIN = 9;
  const USERLEVEL_USER  = 1;

  /**
   * Get this peer instance to access the base methods.
   * This function must be copied in each peer class.
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Find one user by given criteria and return all data for it.
   * 
   * Returns dates as unix timestamps (can be formatted by php)
   * with the prefix "ts_"
   * 
   * @param  string  $criteria    Column to search
   * @param  mixed   $value       Value to match
   *
   * @return array   User array record, or false
   */
  private static function getUserBy($criteria, $value)
  {
    $select = self::getInstance()->select([
      '*',
      'ts_joindate' => 'UNIX_TIMESTAMP(joindate)',
      'ts_lastlogin' => 'UNIX_TIMESTAMP(lastlogin)']);

    $select->where($criteria . ' = ?', $value)
           ->query();
           
    $db = self::getInstance()->getDb();
    $user = $db->fetch();

    // normalize integers
    if (false !== $user)
    {
      $user['userid'] = (int)$user['userid'];
    }

    return $user;
  }
  
  /**
   * Get user by unique name.
   * 
   * @param string  $username
   *
   * @return  mixed   User data (array) or false (boolean)
   */
  public static function getUser($username)
  {
    return self::getUserBy('username', $username);
  }
  
  /**
   * Get user by unique id.
   * 
   * @param mixed  $userid    Should be numeric, but string is ok
   */
  public static function getUserById($userid)
  {
    return self::getUserBy('userid', $userid);
  }

  /**
   * Get user by email address.
   * 
   * @param string  $email
   */
  public static function getUserByEmail($email)
  {
    return self::getUserBy('email', $email);
  }
  
  /**
   * Get user id for name
   *
   * @return int  User id or false
   */
  public static function getUserId($username)
  {
    $select = self::getInstance()->select('userid')->where('username = ?', $username)->query();
    $db = self::getInstance()->getDb();
    if ($row = $db->fetch())
    {
      return (int) $row['userid'];
    }
    return false;
  }

  /**
   * Lastlogin setter.
   * 
   * Sets lastlogin time to NOW() by default.
   *
   * @param int  $userid
   */
  public static function setLastlogin($userid, $timestamp = null)
  {
    return self::updateUser($userid, ['lastlogin' => $timestamp===null ? new coreDbExpr('NOW()') : $timestamp]);
  }

  /**
   * Checks if username is registered.
   *
   * @return boolean True if username is already registered.
   */
  public static function usernameExists($username)
  {
    return (self::getInstance()->count('username = ?', $username) > 0);
  }

  /**
   * Insert a new user record.
   * 
   * The raw password can be any length, since the result will be a fixed length hash.
   * 
   * Required information:
   *   username
   *   raw_password
   *   email
   *   location
   * 
   * Optional:
   *   userlevel
   *   regip                OPTIONAL because of createuser CLI
   *   
   * @param array $userinfo  Assoc.array of form registration data
   */
  public static function createUser(array $userinfo)
  {
    $user = sfContext::getInstance()->getUser();
    $hashed_password = $user->getSaltyHashedPassword($userinfo['raw_password']);


    $userdata = [
      'username'      => $userinfo['username'],
      'password'      => $hashed_password,
      'email'         => $userinfo['email'],
      'location'      => $userinfo['location'],
      'joindate'      => new coreDbExpr('NOW()')
    ];

    // automatically set all new users to the last edition
    $userdata['opt_sequence'] = rtkIndex::getDefaultUserSequence();

    // registration IP won't be set by createuser CLI tool
    $userdata['regip'] = isset($userinfo['regip']) ? $userinfo['regip'] : '';

    // may be explicitly set by maintenance tools
    if (isset($userinfo['userlevel'])) {
      $userdata['userlevel'] = $userinfo['userlevel'];
    }

    return self::getInstance()->insert($userdata);
  }

  /**
   * Delete a user account.
   *
   * Deletes everything! Stories, reviews, votes, etc
   *
   * Returns associative array with info on success:
   *   'stories'     =>  number of stories deleted
   *   'flashcards'  =>  number of flashcards deleted (ReviewsPeer)
   *   'keywords'    =>  number of keywords deleted (CustkeywordsPeer)
   * 
   * @param  int   $userid 
   *
   * @return mixed  Returns an assoc.array with some info (see above), FALSE on failure
   */
  public static function deleteUser($userid)
  {
    $db = self::getInstance()->getDb();

    $result = true;
    $deleteStmt = 'DELETE FROM %s WHERE userid = ?';
    $where = 'userid = ?';
    $count = [];

    // This one can potentially delete 2000+ records at once
    $table = ReviewsPeer::getInstance()->getName();
    $stmt = new coreDatabaseStatementMySQL($db, sprintf($deleteStmt, $table));
    $result = $result && $stmt->execute([$userid]);
    $count['flashcards'] = $stmt->rowCount();

    // This one also can potentially delete 2000+ rows at once
    $table = StoriesPeer::getInstance()->getName();
    $stmt = new coreDatabaseStatementMySQL($db, sprintf($deleteStmt, $table));
    $result = $result && $stmt->execute([$userid]);
    $count['stories'] = $stmt->rowCount();

    // Custom Keywords
    $table = CustkeywordsPeer::getInstance()->getName();
    $stmt = new coreDatabaseStatementMySQL($db, sprintf($deleteStmt, $table));
    $result = $result && $stmt->execute([$userid]);
    $count['keywords'] = $stmt->rowCount();

    // Other misc linked tables
    $table = ActiveMembersPeer::getInstance()->getName();
    $result = $result && $db->delete($table, $where, $userid);
    $table = LearnedKanjiPeer::getinstance()->getname();
    $result = $result && $db->delete($table, $where, $userid);
    $table = StoriesSharedPeer::getInstance()->getName();
    $result = $result && $db->delete($table, $where, $userid);
    $table = StoryVotesPeer::getInstance()->getName();
    $result = $result && $db->delete($table, $where, $userid);
    $table = UsersSettingsPeer::getInstance()->getName();
    $result = $result && $db->delete($table, $where, $userid);

    // Delete the user last in case some sql above breaks, user can still sign in
    $table = UsersPeer::getInstance()->getName();
    $result = $result && $db->delete($table, $where, $userid);

    if (true !== $result)
    {
      throw new sfException("An error occured while deleting user id {$userid}");
    }

    return ($result === false) ? false : $count;

  }

  /**
   * Update columns in user record.
   * 
   * Data must be trimmed and validated!
   *
   * 'raw_password' will be hashed into 'password'.
   * 
   * @param  int   $userid
   * @param  array $columns  Column data
   * 
   * @return boolean
   */
  public static function updateUser($userid, $columns)
  {
    if (isset($columns['raw_password']))
    {
      // hash password for database
      $user = sfContext::getInstance()->getUser();
      $columns['password'] = $user->getSaltyHashedPassword($columns['raw_password']);
      unset($columns['raw_password']);
    }

    return self::getInstance()->update($columns, 'userid = ?', $userid);
  }

  /**
   * Returns number of registrations by given IP in last N hours interval.
   *
   * @param   string  Unpacked ip
   * @param   int     Interval to check in hours
   */
  public static function getRegistrationCount($regip, $since_hours)
  {
    // SELECT COUNT(*) FROM users WHERE (regip = '127.0.0.1') AND (joindate > DATE_SUB(NOW(), INTERVAL 24 HOUR))

    $select = self::getInstance()
      ->select(['count' => 'COUNT(*)'])
      ->where('regip = ?', $regip)
      ->where('joindate > DATE_SUB(NOW(), INTERVAL ? HOUR)', (int)$since_hours);

    $db = self::getInstance()->getDb();
    return (int)$db->fetchOne($select);
  }

  /**
   * Returns server timestamp adjusted to user's timezone, as a SQL expression.
   *
   * The date returned by this statement will switch at midnight time of the user's timezone
   * (assuming the user set the timezone properly).
   * (the user's timezone range is -12...+14)
   * 
   * @return String   MySQL ADDDATE() expression that evaluates to the user's localized time
   */
  public static function sqlLocalTime($time = 'NOW()')
  {
    $user = sfContext::getInstance()->getUser();
    $localTimezone = $user->getUserTimeZone();
    $timediff = $localTimezone - sfConfig::get('app_server_timezone');
    $hours = floor($timediff);

    // some timezones have half-hour precision, convert to minutes
    $minutes = ($hours != $timediff) ? '30' : '0';

    $sqlDate = sprintf('ADDDATE(%s, INTERVAL \'%d:%d\' HOUR_MINUTE)', $time, $hours, $minutes);
    return $sqlDate;
  }

  /**
   * Returns self::sqlLocalTime() as the actual timestamp integer (one query).
   * 
   * Currently, this timestamp corresponds to the adjusted flashcard's "lastreview" timestamp.
   * 
   * @return  int    A TIMESTAMP value.
   */
  public static function intLocalTime()
  {
    $user = sfContext::getInstance()->getUser();

    $db = self::getInstance()->getDb();
    $ts = $db->fetchOne('SELECT UNIX_TIMESTAMP(?)', new coreDbExpr(UsersPeer::sqlLocalTime()));
    if (!$ts) {
      throw new sfException('getLocalizedTimestamp() failed');
    }
    
    return $ts;
  }
}
