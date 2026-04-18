<?php
/**
 * rtkUser adds utility methods to the sfUser class.
 *
 * Methods
 *   getUserId()
 *   getUserName()
 *   getUserTimeZone()
 *   getUserSequence()
 *   getUserKnownKanji()        Cache a string of all the kanji known by user.
 *
 *   getUserSetting()           Proxy and cache for UsersSettingsPeer (account settings)
 *   setUserSetting()
 *   addUserSettings()        Cache application setting(s) (cf. UserSettingsPeer)
 *
 *   getUserDetails()           Get UsersPeer record for the authorized user
 *   getLocalPrefs()
 *
 *   signIn()
 *   signOutAndClearCookie()
 *   isAdministrator()
 *
 *   setRememberMeCookie()
 *   clearRememberMeCookie()
 *
 *   changePassword()
 *   getSaltyHashedPassword()
 *
 *   redirectToLogin()          Redirect unauthenticated user to login page
 *
 * @author     Fabrice Denis
 */
class rtkUser extends sfBasicSecurityUser
{
  protected LocalPrefs $localPrefs;

  // The "Remember me" cookie lifetime in seconds
  public const COOKIE_EXPIRE = 60 * 60 * 24 * 365; // 1 year

  public const CREDENTIAL_ADMIN  = 'admin';
  public const CREDENTIAL_MEMBER = 'member';

  // misc. session attributes
  public const KNOWN_KANJI = 'kanji.known';

  /**
   * @param array $options
   */
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = [])
  {
    parent::initialize($dispatcher, $storage, $options);

    // sign in unauthenticated user if a "remember me" cookie exists
    if (!$this->isAuthenticated()) {
      $cookieName = sfConfig::get('app_cookie_name');
      if ($cookieData = ($_COOKIE[$cookieName] ?? null)) {
        $value         = unserialize(base64_decode($cookieData));
        $username      = $value[0];
        $saltyPassword = $value[1];

        // sign in user if user is valid and password from cookie matches the one in database
        $user = UsersPeer::getUser($username);
        if ($user && ($saltyPassword == $user['password'])) {
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
  public function getUserName(): string
  {
    return $this->getAttribute('username', '');
  }

  public function getUserId(): ?int
  {
    $uid = $this->getAttribute('userid');

    return null !== $uid ? (int) $uid : null;
  }

  public function getUserTimeZone(): mixed
  {
    return $this->getAttribute('usertimezone');
  }

  public function getUserSequence(): mixed
  {
    return $this->getAttribute('usersequence', 0);
  }

  /**
   * Known kanji: cache a string of all the kanji known by user.
   *
   * @param bool $refresh set true to refresh the cache
   */
  public function getUserKnownKanji(bool $refresh = false): string
  {
    if ($refresh || null === ($knownKanji = $this->getAttribute(self::KNOWN_KANJI))) {
      $knownKanji = ReviewsPeer::getKnownKanji($this->getUserId());
      $this->setAttribute(self::KNOWN_KANJI, $knownKanji);
    }

    return $knownKanji;
  }

  // [listener] atm the query is fast, so no add/delete/etc, just invalidate the cached data
  public static function eventUpdateUserKnownKanji(sfEvent $event)
  {
    kk_get_user()->getAttributeHolder()->remove(self::KNOWN_KANJI);
  }

  /**
   * Get a user setting from the user session. Cache them if not done yet.
   *
   * @see UsersSettingsPeer
   *
   * @param string $name Setting name (see OPT_xxx in UsersSettingsPeer)
   *
   * @return mixed Normalized setting value (numbers and booleans as integer)
   */
  public function getUserSetting(string $name): mixed
  {
    $attrName = $this->getUserSettingName($name);

    // return setting if already in session
    if (null !== ($value = $this->getAttribute($attrName))) {
      return $value;
    }

    // otherwise get all settings and cache them
    $settings = UsersSettingsPeer::getUserSettings($this->getUserId());
    $this->addUserSettings($settings);

    return $this->getAttribute($attrName);
  }

  /**
   * Sets the cached (session) value of a user setting.
   *
   * @see UsersSettingsPeer
   */
  public function setUserSetting(string $name, mixed $value)
  {
    $attrName = $this->getUserSettingName($name);
    $this->setAttribute($attrName, $value);
  }

  /**
   * Sets an array of settings at once.
   *
   * @see UsersSettingsPeer
   */
  public function addUserSettings(array $settings)
  {
    foreach ($settings as $name => $value) {
      $this->setUserSetting($name, $value);
    }
  }

  private function getUserSettingName(string $setting): string
  {
    return 'usersetting.'.$setting;
  }

  /**
   * Proxy method to set multiple attributes.
   */
  public function setAttributes(array $attrs)
  {
    $this->getAttributeHolder()->add($attrs);
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
   */
  public function getLocalPrefs(): LocalPrefs
  {
    return $this->localPrefs;
  }

  /**
   * Sign In the user.
   *
   * @param array $user UsersPeer row
   */
  public function signIn(array $user)
  {
    $this->setAttributes([
      // user account settings
      'userid'       => $user['userid'],
      'username'     => $user['username'],
      'usertimezone' => $user['timezone'],
      // user application settings
      'usersequence' => $user['opt_sequence'],
    ]);

    $this->clearCredentials();
    $this->addCredential(self::CREDENTIAL_MEMBER);

    switch ($user['userlevel']) {
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

  public function isAdministrator(): bool
  {
    return $this->hasCredential(self::CREDENTIAL_ADMIN);
  }

  /**
   * Sets the persistent session cookie.
   */
  public function setRememberMeCookie(string $username, string $saltyPassword)
  {
    $value = base64_encode(serialize([$username, $saltyPassword]));
    kk_get_response()->setCookie(sfConfig::get('app_cookie_name'), $value, (string) (time() + self::COOKIE_EXPIRE), '/');
  }

  /**
   * Clears the persistent session cookie.
   */
  public function clearRememberMeCookie()
  {
    kk_get_response()->setCookie(sfConfig::get('app_cookie_name'), '', (string) (time() - 3600), '/');
  }

  /**
   * Update the user password in the main site and forum databases.
   */
  public function changePassword(string $username, string $raw_password)
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
   * @param string $raw_password non-encrypted password
   */
  public function getSaltyHashedPassword(string $raw_password): string
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
   * @param array $options Options to pass to the login page
   */
  public function redirectToLogin(array $options = [])
  {
    if (isset($options['referer'])) {
      $this->setAttribute('login_referer', $options['referer']);
    }

    if (isset($options['username'])) {
      $this->setAttribute('login_username', $options['username']);
    }

    $login_url = sfConfig::get('sf_login_module').'/'.sfConfig::get('sf_login_action');
    sfContext::getInstance()->getController()->getActionStack()->getLastEntry()->getActionInstance()->redirect($login_url);
  }
}
