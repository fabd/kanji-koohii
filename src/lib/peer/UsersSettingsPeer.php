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
    'no_shuffle' => 'OPT_NO_SHUFFLE',
    'srs_max_box' => 'OPT_SRS_MAX_BOX',
    'srs_mult' => 'OPT_SRS_MULT',
    'srs_hard_box' => 'OPT_SRS_HARD_BOX',
  ];

  private static $defaultSettings = [
    'OPT_NO_SHUFFLE' => 0,    // do not shuffle new cards (blue pile)
    'OPT_SRS_MAX_BOX' => 7,   // number of review piles (excludes Failed & New box)
    'OPT_SRS_MULT' => 205,    // 205 means 2.05
    'OPT_SRS_HARD_BOX' => 0,  // zero means default behaviour
  ];

  public static function getInstance(): self
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Swap keys in $data from SQL column names to settings names, or vice versa.
   *
   * @param array $settings ... row data (one mor more columns)
   * @param bool  $reverse
   *
   * @return array
   */
  private static function mapKeys(array $data, bool $reverse = false)
  {
    $map = $reverse ? array_flip(self::$map) : self::$map;
    $mapped_keys =array_map(fn($k) => $map[$k], array_keys($data)); 
    return array_combine($mapped_keys, $data);
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
    $select = self::getInstance()
      ->select(array_keys(self::$map))
      ->where('userid = ?', $userId)->query();

    if ($row = self::$db->fetch())
    {
      $settings = self::mapKeys($row);
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
    return self::getInstance()->count('userid = ?', $userId) > 0;
  }

  public static function saveUserSettings($userId, array $settings)
  {
    // if the settings aren't saved yet, fill in the defaults
    if (!self::hasUserSettings($userId))
    {
      $defaults = self::getDefaultSettings();
      $settings = array_merge($defaults, $settings);
      $colData = self::mapKeys($settings, true);

      $colData['userid'] = $userId;

      return self::getInstance()->insert($colData);
    }
    else
    {
      $colData = self::mapKeys($settings, true);

      return self::getInstance()->update($colData, 'userid = ?', $userId);
    }
  }
}
