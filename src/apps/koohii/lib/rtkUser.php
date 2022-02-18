<?php
/**
 * rtkUser adds utility methods to the sfUser class.
 *
 * Methods
 *   getUserId()
 *   getUserName()
 *   getUserTimeZone()
 *   getUserSequence()
 *
 *   getUserSetting()                Proxy and cache for UsersSettingsPeer (account settings)
 *   setUserSetting()
 *   cacheUserSettings($settings)    Cache application setting(s) (cf. UserSettingsPeer)
 *
 *   getUserDetails()                Get UsersPeer record for an authorized user
 *   getLocalPrefs()
 *
 *   isAdministrator()
 *
 *   signIn()
 *   signOut()
 *
 *   changePassword($username, $raw_password)
 *   getSaltyHashedPassword($raw_password)
 *
 *   redirectToLogin($options = array())      Redirect unauthenticated user to login page
 *
 * @author     Fabrice Denis
 */
class rtkUser extends sfBasicSecurityUser
{
  /** @var LocalPrefs */
  protected $localPrefs;

  // The "Remember me" cookie name and lifetime in seconds.
  public const COOKIE_EXPIRE = 31536000; // 60*60*24*365

  public const CREDENTIAL_ADMIN = 'admin';
  public const CREDENTIAL_MEMBER = 'member';

  // Misc. states that do not need database permanence
  public const IS_RESTUDY_SESSION = 'study.restudy.start';

  // misc. session attributes
  public const KNOWN_KANJI = 'kanji.known';

  /**
   * @param array $options
   */
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = [])
  {
    parent::initialize($dispatcher, $storage, $options);

    // sign in unauthenticated user if a "remember me" cookie exists
    if (!$this->isAuthenticated())
    {
      if ($cookieData = sfContext::getInstance()->getRequest()->getCookie(sfConfig::get('app_cookie_name')))
      {
        $value = unserialize(base64_decode($cookieData));
        $username = $value[0];
        $saltyPassword = $value[1];

        // sign in user if user is valid and password from cookie matches the one in database
        $user = UsersPeer::getUser($username);
        if ($user && ($saltyPassword == $user['password']))
        {
          $this->signIn($user);
        }
      }
    }

    // session duration preferences
    $this->localPrefs = new LocalPrefs($this);
  }

  /**
   * Getters for user attributes.
   */
  public function getUserName()
  {
    return $this->getAttribute('username', '');
  }

  // returns int | null
  public function getUserId()
  {
    $uid = $this->getAttribute('userid', null);

    return null !== $uid ? (int) $uid : null;
  }

  public function getUserTimeZone()
  {
    return $this->getAttribute('usertimezone', null);
  }

  public function getUserSequence()
  {
    return $this->getAttribute('usersequence', 0);
  }

  /**
   * Known kanji: cache a string of al the kanji known by user.
   *
   * @param bool $refresh set true to refresh the cache
   */
  public function getUserKnownKanji($refresh = false)
  {
    if (!$refresh && null !== ($knownKanji = $this->getAttribute(self::KNOWN_KANJI, null)))
    {
      return $knownKanji;
    }

    $knownKanji = ReviewsPeer::getKnownKanji($this->getUserId());
    $this->setAttribute(self::KNOWN_KANJI, $knownKanji);

    return $knownKanji;
  }

  // [listener] atm the query is fast, so no add/delete/etc, just invalidate the cached data
  public static function eventUpdateUserKnownKanji(sfEvent $event)
  {
    $user = sfContext::getInstance()->getUser();
    $user->getAttributeHolder()->remove(self::KNOWN_KANJI);
  }

  /**
   * Get a user setting. If not found in the session, cache all settings from
   * UsersSettingsPeer table.
   *
   * @param string $setting Setting name (UsersSettingsPeer::OPT_xxx)
   *
   * @return mixed Normalized settings (numbers and booleans as integer)
   */
  public function getUserSetting($setting)
  {
    $attrName = $this->getUserSettingName($setting);

    // return setting if already in session
    if (null !== ($value = $this->getAttribute($attrName)))
    {
      return $value;
    }

    // otherwise get all settings and cache them
    $settings = UsersSettingsPeer::getUserSettings($this->getUserId());
    $this->cacheUserSettings($settings);

    return $this->getAttribute($attrName);
  }

  /**
   * Sets the cached (session) value of a user setting.
   *
   * @param string $name  Setting name (UserSettingsPeer constant)
   * @param string $value
   */
  public function setUserSetting($name, $value)
  {
    $attrName = $this->getUserSettingName($name);
    $this->setAttribute($attrName, $value);
  }

  private function getUserSettingName($setting)
  {
    return 'usersetting.'.$setting;
  }

  public function cacheUserSettings(array $settings)
  {
    foreach ($settings as $name => $value)
    {
      $this->setUserSetting($name, $value);
    }
  }

  /**
   * Proxy method to set one or more attributes.
   *
   * @param array $attrs
   */
  public function setAttributes($attrs)
  {
    $valid_attributes = ['userid', 'username', 'usertimezone', 'usersequence'];
    if (!rtkValidators::validateArrayKeys($attrs, $valid_attributes))
    {
      throw new sfException('Invalid attribute passed to '.__METHOD__);
    }

    foreach ($attrs as $key => $value)
    {
      $this->setAttribute($key, $value);
    }
  }

  /**
   * Return UsersPeer row data for authenticated user.
   */
  public function getUserDetails()
  {
    return UsersPeer::getUserById($this->getUserId());
  }

  /**
   * Return the LocalPrefs instance.
   *
   * @return
   */
  public function getLocalPrefs()
  {
    return $this->localPrefs;
  }

  /**
   * Sign In the user.
   *
   * @param array $user UsersPeer row
   *
   * @return
   */
  public function signIn($user)
  {
    $this->setAttributes([
      // user account settings
      'userid' => $user['userid'],
      'username' => $user['username'],
      'usertimezone' => $user['timezone'],
      // user application settings
      'usersequence' => $user['opt_sequence'],
    ]);

    $this->clearCredentials();
    $this->addCredential(self::CREDENTIAL_MEMBER);

    switch ($user['userlevel'])
    {
      case UsersPeer::USERLEVEL_ADMIN:
        $this->addCredential(self::CREDENTIAL_ADMIN);

        break;

      default:
        break;
    }

    // authenticate the user
    $this->setAuthenticated(true);

    // update user's last login timestamp
    UsersPeer::setLastlogin($user['userid']);
  }

  public function signOutAndClearCookie()
  {
    $this->getAttributeHolder()->clear();
    $this->clearCredentials();
    $this->setAuthenticated(false);

    $this->clearRememberMeCookie();
  }

  public function isAdministrator()
  {
    return $this->hasCredential(self::CREDENTIAL_ADMIN);
  }

  /**
   * Sets the persistent session cookie.
   *
   * @param mixed $username
   * @param mixed $saltyPassword
   */
  public function setRememberMeCookie($username, $saltyPassword)
  {
    $value = base64_encode(serialize([$username, $saltyPassword]));
    sfContext::getInstance()->getResponse()->setCookie(sfConfig::get('app_cookie_name'), $value, time() + self::COOKIE_EXPIRE, '/');
  }

  /**
   * Clears the persistent session cookie.
   *
   * @return
   */
  public function clearRememberMeCookie()
  {
    sfContext::getInstance()->getResponse()->setCookie(sfConfig::get('app_cookie_name'), '', time() - 3600, '/');
  }

  /**
   * Update the user password in the main site and forum databases.
   *
   * @param string $user
   * @param string $raw_password
   * @param mixed  $username
   */
  public function changePassword($username, $raw_password)
  {
    $user_id = UsersPeer::getUserId($username);

    $columns = ['raw_password' => $raw_password];

    UsersPeer::updateUser($user_id, $columns);
  }

  /**
   * Returns hashed password.
   *
   * We use sha1() like PunBB to store passwords.
   *
   * Ideally could store a random salt with each user, eg:
   *
   *   salt VARCHAR(32)      =>  md5(rand(100000, 999999).$this->getNickname().$this->getEmail());
   *   password VARCHAR(40)  =>  sha1($salt.$raw_password)
   *
   * @param string $password     non-encrypted password
   * @param mixed  $raw_password
   */
  public function getSaltyHashedPassword($raw_password)
  {
    return sha1($raw_password);
  }

  /**
   * Redirect unauthenticated user to login action.
   *
   * Options:
   *
   *   username => Fill in the user name of the login form
   *   referer  => Page to return the user to after signing in
   *
   * @param array $params  Options to pass to the login page
   * @param mixed $options
   */
  public function redirectToLogin($options = [])
  {
    if (isset($options['referer']))
    {
      $this->setAttribute('login_referer', $options['referer']);
    }

    if (isset($options['username']))
    {
      $this->setAttribute('login_username', $options['username']);
    }

    $login_url = sfConfig::get('sf_login_module').'/'.sfConfig::get('sf_login_action');
    sfContext::getInstance()->getActionInstance()->redirect($login_url);
  }
}
