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
    $this->db = sfProjectConfiguration::getActive()->getDatabase();
  }

  public function logUserDeletion(int $userid, string $username, int $joindate, string $description = '')
  {
    // trim the table while we're here
    $this->trim();

    $logtime = time();

    $this->db->insert(self::LOG_TABLE_NAME, [
      'userid' => $userid,
      'username' => $username,
      'joindate' => $joindate,
      'logtime' => $logtime,
      'logdesc' => $description,
    ]);
  }

  public function getSelect()
  {
    return $this->db->select()->from(self::LOG_TABLE_NAME);
  }

  // clean the log by deleting all entries older than N days.
  public function trim()
  {
    $mintime = time() - (self::LOG_PERSIST_DAYS * 24 * 60 * 60);
    $this->db->delete(self::LOG_TABLE_NAME, 'logtime < ?', $mintime);
  }
}
