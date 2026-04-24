#!/usr/bin/php -C -q
<?php
/**
 * Edit user account (maintenance).
 *
 * Use "--env prod" or "--env staging" on production site, otherwise it defaults to "dev".
 *
 * Watchout for shell escaping, use single quotes around the password: --cp 'foo!&bar%$'.
 *
 * Show user info:
 *  $ edituser [--env prod] -v -u <username>
 *
 * Change user password:
 *  $ edituser [--env prod] -v -u <username> --cp <password>
 *
 * Change user name:
 *  $ edituser [--env prod] -v -u <username> --cu <new_name>
 *
 * Delete user account:
 *  $ edituser [--env prod] -v -u <username> --da
 *
 * For help:
 *   $ edituser
 *
 *
 * WARNING!
 * - Does not validate against the website's password restrictions! (todo)
 *
 * @author  Fabrice Denis
 */

require_once realpath(dirname(__FILE__).'/../..').'/lib/Batch/Command_CLI.php';

class EditUser_CLI extends Command_CLI
{
  public function __construct()
  {
    parent::__construct([
      'user|u=s' => 'Username of the user to edit',
      'cp=s'     => 'Change password (use single quotes eg. --p \'foo&bar%$\')',
      'cu=s'     => 'Change username (WARNING! No validation!)',
      'da'       => 'Delete account (WARNING! NO CONFIRMATION!)',

      // temporary
      'tud'   => 'Transer user data (temp)',
      'tui=i' => 'Transer user data to: userid',
    ]);

    // Verify we're on the correct database
    // print_r(coreConfig::get('database_connection'));exit;

    $connectionInfo = sfConfig::get('app_db_connection');
    $this->verbose('Using database: %s', $connectionInfo['database']);

    // required
    $username = trim($this->getFlag('u'));

    // get user id
    $userid = UsersPeer::getUserId($username);
    if (false === $userid) {
      $this->throwError('User "%s" not found.', $username);
    }

    $this->echof('Userid: %s', $userid);

    // change password
    if (false !== ($c_password = $this->getFlag('cp', false))) {
      $this->doChangePassword($userid, $username, trim($c_password));

      return;
    }

    // change username
    if (false !== ($c_username = $this->getFlag('cu', false))) {
      $this->doChangeUsername($userid, $username, trim($c_username));

      return;
    }

    // delete account
    if ($this->getFlag('da', false)) {
      $this->doDeleteAccount($userid);

      return;
    }

    if (false !== $this->getFlag('tud', false)) {
      if (0 !== ($to_user_id = (int) $this->getFlag('tui', 0))) {
        $this->verbose(' ... replacing user id %d => %d', $userid, $to_user_id);
      }
      $this->doExportUserData($userid, $username, $to_user_id);

      return;
    }

    // show user info
    $this->doShowUserInfo($userid);
  }

  protected function doShowUserInfo(int $userid): void
  {
    $userinfo = UsersPeer::getUserById($userid);

    $userinfo['NUM_STORIES']    = StoriesPeer::getNumStories($userid);
    $userinfo['NUM_FLASHCARDS'] = ReviewsPeer::getFlashcardCount($userid);

    $this->echoKeyValues($userinfo);
  }

  protected function doDeleteAccount(int $userid): void
  {
    // throws if it fails for whatever reason
    $stats = UsersPeer::deleteUser($userid);

    $this->echof(' User account deleted!');
    $this->echof('  %s stories deleted (StoriesPeer)', $stats['stories']);
    $this->echof('  %s flashcards deleted (ReviewsPeer)', $stats['flashcards']);
  }

  protected function doChangeUsername(int $userid, string $username, string $c_username): void
  {
    $this->verbose('Set username to: %s', $c_username);

    if (false !== UsersPeer::getUserId($c_username)) {
      $this->throwError('Username "%s" is already registered!', $c_username);
    }

    $columns = ['username' => trim($c_username)];
    if (false === UsersPeer::updateUser($userid, $columns)) {
      $this->throwError('Could not update user "%s" (userid %s)', $username, $userid);
    }

    $this->echof(' Username updated.');
  }

  protected function doChangePassword(int $userid, string $username, string $c_password): void
  {
    $this->verbose('Set password to: %s', $c_password);

    // UsersPeer::update() will hash the password
    $columns = ['raw_password' => $c_password];

    if (false === UsersPeer::updateUser($userid, $columns)) {
      $this->throwError('UsersPeer::updateUser("%s")', $username);
    }
    $this->echof(' Main  password updated.');
  }

  /**
   * Export flashcards and stories to SQL files that can be imported with LOAD
   * DATA INFILE.
   *
   * @param int    $userid     User id from
   * @param string $username   User name from
   * @param int    $to_user_id User id that will be replaced into the output files
   *
   * Transfer RevTH user data:
   *
   *   $ edituser -v -u guest --tud --tui 2 --rth
   */
  protected function doExportUserData(int $userid, string $username, int $to_user_id): void
  {
    $this->verbose('Exporting user data from: %s', $username);

    $this->exportRowsInto('reviews', $userid, $username.'_fc.sql', $to_user_id);
    $this->exportRowsInto('stories', $userid, $username.'_st.sql', $to_user_id);
  }

  /**
   * Export all rows from table where userid = $fromUserId, optionally replacing 'userid' col with $toUserId.
   *
   * Note: LOAD DATA default = ESCAPE BY '\\'
   */
  private function exportRowsInto(string $tableName, int $fromUserId, string $fileName, int $toUserId): void
  {
    $db     = kk_get_database();
    $select = $db->select('*')->from($tableName)->where('userid = ?', $fromUserId)->query();

    $handle = $this->fileOpen($fileName, 'wb');
    $count  = 0;

    while (false !== ($row = $db->fetch())) {
      if ($toUserId) {
        $row['userid'] = $toUserId;
      }

      if ($tableName === 'stories') {
        $row['text'] = addcslashes($row['text'], "\t\n\r\\");
      }

      if ($tableName === 'reviews') {
        $row = [
          $row['userid'], $row['ucs_id'],
          '0000-00-00 00:00:00', // created_on
          $row['lastreview'], $row['expiredate'], $row['totalreviews'], $row['leitnerbox'], $row['failurecount'], $row['successcount'],
        ];
      }

      $this->fileOutputArray($handle, $row);
      $count++;
    }

    $this->verbose('... %d rows exported to "%s".', $count, $fileName);
    $this->fileClose($handle);
  }

  /**
   * @param array<string, mixed> $data
   */
  private function echoKeyValues(array $data): void
  {
    // dump($data);return;

    foreach ($data as $sKey => $sValue) {
      $this->echof("\033[1;32m%16s\033[0m ... %s", $sKey, $sValue);
    }
  }

  /**
   * @param resource         $fileHandle
   * @param array<int,mixed> $rowData
   */
  public function fileOutputArray($fileHandle, array $rowData): void
  {
    $line = implode("\t", $rowData)."\n";
    fwrite($fileHandle, $line);
  }

  /**
   * @return resource
   */
  public function fileOpen(string $fileName, string $mode = 'r')
  {
    $handle = @fopen($fileName, $mode);

    if (false === $handle) {
      $this->throwError('Error opening file: "%s" with mode "%s"', $fileName, $mode);
    }

    return $handle;
  }

  /**
   * @param resource $fileHandle
   */
  public function fileClose($fileHandle): void
  {
    fclose($fileHandle);
  }
}

$cmd = new EditUser_CLI();
