<?php
/**
 * Add a user to the database.
 * 
 * USAGE
 *
 *   Get basic account info
 *   $ php batch/maintenance/createUser.php -u "<username>"
 *
 *   Update a user password
 *   $ php batch/maintenance/createUser.php -u "<username>" -p "<password>"
 *
 * 
 * OPTIONAL
 * 
 *   --email "johndoe@foobar.com"
 *   --location "Canada"
 *   --level <1-9>    (defaults to 1 = USERLEVEL_USER, 9 = USERLEVEL_ADMIN, cf. UsersPeer)
 *
 * 
 * WARNING
 * 
 *   - Does not validate against the website's password restrictions! (todo)
 *
 * @author  Fabrice Denis
 */

require_once(realpath(dirname(__FILE__).'/../..').'/lib/batch/Command_CLI.php');

define('EMAIL_DEFAULT',    'created@localhost');
define('LOCATION_DEFAULT', 'Localhost City');

class CreateUser_CLI extends Command_CLI
{
  public function __construct()
  {
    parent::__construct(array(
      'username|u=s'   => 'Username of the user to create',
      'password|p=s'   => 'Password. Use single quotes to escape shell characters, eg: \'foo&bar%$\'',
      'email|e-s'      => 'Email address (optional, defaults to "'.EMAIL_DEFAULT.'")',
      'location-s'     => 'Location (optional, defaults to "'.LOCATION_DEFAULT.'")',
      'level-s'        => 'User level. Defaults to 1 (USERLEVEL_USER). 9 is USERLEVEL_ADMIN (see UsersPeer)'
    ));
    
    $connectionInfo = sfConfig::get('app_db_connection');
    $this->verbose("Using database: %s", $connectionInfo['database']);

    $username = $this->getFlag('username');
    $raw_password = $this->getFlag('password');

    if (empty($username) || empty($raw_password))
    {
      $this->throwError('Username or password is empty.');
    }

    if (UsersPeer::usernameExists($username))
    {
      $this->throwError('That username is already taken.');
    }

    $this->createUser($username, $raw_password);
  }

  private function createUser($username, $raw_password)
  {
    $userinfo = array(
      'username'     => $username,
      'raw_password' => $raw_password,
      'userlevel'    => $this->getFlag('level', UsersPeer::USERLEVEL_USER),
      'email'        => trim($this->getFlag('email', EMAIL_DEFAULT)),
      'location'     => trim($this->getFlag('location', LOCATION_DEFAULT)),
      'regip'        => '00.111.222.333'
    );
    
    //die(print_r($userinfo, true));

    if (false === UsersPeer::createUser($userinfo))
    {
      $this->throwError('Could not create user.');
    }

    $this->echof('Succesfully created user "%s" with password "%s".', $username, $raw_password);
  }
}

$cmd = new CreateUser_CLI();
