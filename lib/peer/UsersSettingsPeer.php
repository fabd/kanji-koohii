<?php
/**
 * The User Settings table stores various settings/options related to the
 * application features (rather than the user profile).
 * 
 * The settings are cached in the user attributes the first time one is
 * accessed via rtkUser::getUserSetting().
 *
 * 
 * Methods:
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
    'OPT_NO_SHUFFLE'   => 'no_shuffle',
    'OPT_READINGS'     => 'show_onkun',

    'OPT_SRS_MAX_BOX'  => 'srs_max_box',
    'OPT_SRS_MULT'     => 'srs_mult',
    'OPT_SRS_HARD_BOX' => 'srs_hard_box'
  );

  private static $defaultSettings = array(
    'OPT_NO_SHUFFLE'   => 0,    // do not shuffle new cards (blue pile)
    'OPT_READINGS'     => 0,    // do not show example words in flashcard reviews

    'OPT_SRS_MAX_BOX'  => 7,    // num intervals (excludes Failed & New box)
    'OPT_SRS_MULT'     => 205,  // 205 means 2.05
    'OPT_SRS_HARD_BOX' => 0     // zero means default behaviour
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
    $select  = self::getInstance()->select('*')->where('userid = ?', $userId)->query();
    if ($row = self::$db->fetch())
    {
      // fab: not worth making more fancy code atm ...
      $settings = array(
        'OPT_NO_SHUFFLE'   => $row['no_shuffle'],
        'OPT_READINGS'     => $row['show_onkun'],

        'OPT_SRS_MAX_BOX'  => $row['srs_max_box'],
        'OPT_SRS_MULT'     => $row['srs_mult'],
        'OPT_SRS_HARD_BOX' => $row['srs_hard_box']
      );
    }
    else
    {
      $settings = self::getDefaultSettings();
    }

    // normalize the settings
    $settings['OPT_NO_SHUFFLE']   = intval($settings['OPT_NO_SHUFFLE']);
    $settings['OPT_READINGS']     = intval($settings['OPT_READINGS']);

    $settings['OPT_SRS_MAX_BOX']  = intval($settings['OPT_SRS_MAX_BOX']);
    $settings['OPT_SRS_MULT']     = intval($settings['OPT_SRS_MULT']);
    $settings['OPT_SRS_HARD_BOX'] = intval($settings['OPT_SRS_HARD_BOX']);

    return $settings;
  }
  
  public static function hasUserSettings($userId)
  {
    $count = self::getInstance()->count('userid = ?', $userId);
    return (bool)$count;
  }

  public static function saveUserSettings($userId, array $settings)
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
