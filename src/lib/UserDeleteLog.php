<?php
/**
 * Simple log of user account deletion.
 *
 * ./data/schemas/incremental/rtk_0018...
 */
class UserDeleteLog
{
  // how many days back to keep in the logs
  const LOG_PERSIST_DAYS = 365;

  // name of the log table
  const LOG_TABLE_NAME = 'log_user_delete';

  public function __construct()
  {
    $this->db = kk_get_database();
  }

  /**
   * @param string $joindate from user.joindate DATETIME (server timezone)
   */
  public function logUserDeletion(int $userid, string $username, string $joindate, string $description = '')
  {
    // trim the table while we're here
    $this->trim();

    $logtime = time();

    $this->db->insert(self::LOG_TABLE_NAME, [
      // 'created_on' :: DEFAULT CURRENT_TIMESTAMP
      'userid' => $userid,
      'username' => $username,
      'joindate' => $joindate,
      'logdesc' => $description,
    ]);
  }

  public function getSelectForBackend()
  {
    return $this->db->select([
      'created_on',
      'ts_created_on' => 'UNIX_TIMESTAMP(`created_on`)',
      'userid',
      'username',
      'joindate',
      'ts_joindate' => 'UNIX_TIMESTAMP(`joindate`)',
      'logdesc',
    ])->from(self::LOG_TABLE_NAME)->order('created_on DESC');
  }

  // clean the log by deleting all entries older than N days.
  public function trim()
  {
    $mintime = time() - (self::LOG_PERSIST_DAYS * 24 * 60 * 60);
    $this->db->delete(self::LOG_TABLE_NAME, 'created_on < ?', $mintime);
  }
}
