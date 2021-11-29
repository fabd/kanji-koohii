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
  protected $tableName = 'users_settings';

  // timestamp cols for self::insert/update/replace
  protected $columns = ['created_on', 'updated_on'];

  // array to map settings names to database fields
  public static $map = [
    'OPT_NO_SHUFFLE' => 'no_shuffle',
    'OPT_SRS_MAX_BOX' => 'srs_max_box',
    'OPT_SRS_MULT' => 'srs_mult',
    'OPT_SRS_HARD_BOX' => 'srs_hard_box',
  ];

  private static $defaultSettings = [
    'OPT_NO_SHUFFLE' => 0,    // do not shuffle new cards (blue pile)
    'OPT_SRS_MAX_BOX' => 7,    // num intervals (excludes Failed & New box)
    'OPT_SRS_MULT' => 205,  // 205 means 2.05
    'OPT_SRS_HARD_BOX' => 0,     // zero means default behaviour
  ];

  public static function getInstance(): self
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Convert keys in associative array of settings to column names for database
   * updates.
   *
   * @param array $settings
   * @return array array for insert/updates
   */
  private static function mapSettingsToCols($settings)
  {
    $colData = [];
    foreach ($settings as $name => $value)
    {
      assert(array_key_exists($name, self::$map));
      $colName = self::$map[$name];
      $colData[$colName] = $value;
    }

    return $colData;
  }

  /**
   * @return array
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
   * @param mixed $userId
   *
   * @return array Associative array (col => value) with normalized types
   */
  public static function getUserSettings($userId)
  {
    $select = self::getInstance()->select('*')->where('userid = ?', $userId)->query();
    if ($row = self::$db->fetch())
    {
      // fab: not worth making more fancy code atm ...
      $settings = [
        'OPT_NO_SHUFFLE' => $row['no_shuffle'],
        'OPT_SRS_MAX_BOX' => $row['srs_max_box'],
        'OPT_SRS_MULT' => $row['srs_mult'],
        'OPT_SRS_HARD_BOX' => $row['srs_hard_box'],
      ];
    }
    else
    {
      $settings = self::getDefaultSettings();
    }

    // normalize the settings
    $settings['OPT_NO_SHUFFLE'] = (int) $settings['OPT_NO_SHUFFLE'];
    $settings['OPT_SRS_MAX_BOX'] = (int) $settings['OPT_SRS_MAX_BOX'];
    $settings['OPT_SRS_MULT'] = (int) $settings['OPT_SRS_MULT'];
    $settings['OPT_SRS_HARD_BOX'] = (int) $settings['OPT_SRS_HARD_BOX'];

    return $settings;
  }

  public static function hasUserSettings($userId)
  {
    $count = self::getInstance()->count('userid = ?', $userId);

    return $count > 0;
  }

  public static function saveUserSettings($userId, array $settings)
  {
    // if the settings aren't saved yet, fill in the defaults
    if (!self::hasUserSettings($userId))
    {
      $defaults = self::getDefaultSettings();
      $settings = array_merge($defaults, $settings);
      $colData = self::mapSettingsToCols($settings);
      $colData['userid'] = $userId;

      return self::getInstance()->insert($colData);
    }
    else
    {
      $colData = self::mapSettingsToCols($settings);

      return self::getInstance()->update($colData, 'userid = ?', $userId);
    }
  }
}
