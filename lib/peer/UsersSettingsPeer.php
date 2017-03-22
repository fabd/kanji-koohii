<?php
/**
 * The User Settings table stores various settings/options related to the
 * application features (rather than the user profile). The settings are
 * cached in the user attributes the first time one is accessed through
 * rtkUser::getUserSetting().
 * 
 * Methods:
 *  cacheUserSettings($settings)      Cache setting(s) in the user session (rtkUser).
 *  getDefaultSettings()
 *  getUserSettings($userId)
 *  hasUserSettings($userId)
 *  saveUserSettings($userId, $settings)
 *
 * Private:
 *  mapSettingsToCols($settings)
 */

class UsersSettingsPeer extends coreDatabaseTable
{
  // array to map settings names to database fields
  public static $map = array(
    'OPT_NO_SHUFFLE'  => 'no_shuffle',
    'OPT_READINGS'    => 'show_onkun'
  );

  private static $defaultSettings = array(
    'OPT_NO_SHUFFLE'  => 0,    // do not shuffle new cards (blue pile)
    'OPT_READINGS'    => 0     // do not show example words in flashcard reviews
  );

  protected
    $tableName = 'users_settings',
    $columns   = array('created_on', 'updated_on'); // timestamp columns must be declared for insert/update/replace

  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Convert keys in associative array of settings to column names for database
   * updates.
   *
   * @return  array   Array for insert/updates.
   */
  private static function mapSettingsToCols($settings)
  {
    $colData = array();
//DBG::printr($settings);
    foreach ($settings as $name => $value)
    {
      assert('array_key_exists($name, self::$map)');
      $colName = self::$map[$name];
      $colData[$colName] = $value;
    }

    return $colData;
  }

  /**
   * Cache setting(s) in the user session.
   *
   * @param   array   Settings assoc. array
   */
  public static function cacheUserSettings($settings)
  {
    $user = sfContext::getInstance()->getUser();

    foreach ($settings as $name => $value)
    {
      $user->setUserSetting($name, $value);
    }
  }

  /**
   *
   * @return  array
   */
  public static function getDefaultSettings()
  {
    return self::$defaultSettings;
  }

  /**
   * Get user settings as an associative array. This function normalizes the
   * settings (numbers as integers).
   *
   * @param   int     user id
   *
   * @return  array   Associative array (col => value) with normalized types
   */
  public static function getUserSettings($userId)
  {
    // get the default values
    $settings = self::getDefaultSettings();

    // overwrite with saved settings, if present
    $select  = self::getInstance()->select('*')->where('userid = ?', $userId)->query();
    if ($row = self::$db->fetch())
    {
      $savedSettings = array(
        'OPT_NO_SHUFFLE'  => $row['no_shuffle'],
        'OPT_READINGS'    => $row['show_onkun']
      );

      $settings = array_merge($settings, $savedSettings);
    }

    // normalize the settings
    $settings['OPT_NO_SHUFFLE'] = intval($settings['OPT_NO_SHUFFLE']);
    $settings['OPT_READINGS']   = intval($settings['OPT_READINGS']);

    return $settings;
  }
  
  /**
   * 
   * @return  bool    True if user has saved settings.
   */
  public static function hasUserSettings($userId)
  {
    $count = self::getInstance()->count('userid = ?', $userId);
    return (bool)$count;
  }

  public static function saveUserSettings($userId, $settings)
  {
    // if the settings aren't saved yet, fill in the defaults
    if (!self::hasUserSettings($userId))
    {
      $defaults = self::getDefaultSettings();
      $settings = array_merge($defaults, $settings);
      $colData  = self::mapSettingsToCols($settings);
      $colData['userid'] = $userId;

      return self::getInstance()->insert($colData);
    }
    else
    {
      $colData  = self::mapSettingsToCols($settings);

      return self::getInstance()->update($colData, 'userid = ?', $userId);
    }
  }
}
