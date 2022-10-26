<?php
/**
 * The User Settings table stores various settings/options related to the
 * application features (not the user profile, which is table `users`).
 *
 * The settings are cached in the user attributes the first time one is
 * accessed via rtkUser::getUserSetting().
 *
 *
 * Methods:
 *  getDefaultSettings()
 *
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
    'no_shuffle' => 'OPT_NO_SHUFFLE',
    'srs_max_box' => 'OPT_SRS_MAX_BOX',
    'srs_mult' => 'OPT_SRS_MULT',
    'srs_hard_box' => 'OPT_SRS_HARD_BOX',
    'srs_reverse' => 'OPT_SRS_REVERSE',
  ];

  private static $defaultSettings = [
    'OPT_NO_SHUFFLE' => 0,    // do not shuffle new cards (blue pile)
    'OPT_SRS_MAX_BOX' => 7,   // number of review piles (excludes Failed & New box)
    'OPT_SRS_MULT' => 205,    // 205 means 2.05
    'OPT_SRS_HARD_BOX' => 0,  // zero means default behaviour
    'OPT_SRS_REVERSE' => 0,   // 1 = reverse from Heisig's recommendation
  ];

  public static function getInstance(): self
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Map the key names from SQL cols to user settings names.
   *
   * @return array
   */
  private static function mapColsToOpts(array $data)
  {
    $map = self::$map;
    $mapped_keys = array_map(fn ($k) => $map[$k], array_keys($data));

    return array_combine($mapped_keys, $data);
  }

  /**
   * Map the key names from user settings names to SQL cols.
   *
   * @return array
   */
  private static function mapOptsToCols(array $data)
  {
    $map = array_flip(self::$map);
    $mapped_keys = array_map(fn ($k) => $map[$k], array_keys($data));

    return array_combine($mapped_keys, $data);
  }

  /**
   * @return int[]
   */
  public static function getDefaultSettings()
  {
    return self::$defaultSettings;
  }

  /**
   * Get user settings as an associative array. This function normalizes the
   * settings (numbers as integers).
   *
   * @param int $userId
   *
   * @return array Associative array (col => value) with normalized types
   */
  public static function getUserSettings($userId)
  {
    $select = self::getInstance()
      ->select(array_keys(self::$map))
      ->where('userid = ?', $userId)->query();

    if ($row = self::$db->fetch())
    {
      $settings = self::mapColsToOpts($row);
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
    $settings['OPT_SRS_REVERSE'] = (int) $settings['OPT_SRS_REVERSE'];

    return $settings;
  }

  public static function hasUserSettings($userId)
  {
    return self::getInstance()->count('userid = ?', $userId) > 0;
  }

  public static function saveUserSettings($userId, array $settings)
  {
    // if the settings aren't saved yet, fill in the defaults
    if (!self::hasUserSettings($userId))
    {
      $defaults = self::getDefaultSettings();
      $settings = array_merge($defaults, $settings);
      $colData = self::mapOptsToCols($settings);

      $colData['userid'] = $userId;

      return self::getInstance()->insert($colData);
    }
    else
    {
      $colData = self::mapOptsToCols($settings);

      return self::getInstance()->update($colData, 'userid = ?', $userId);
    }
  }
}
