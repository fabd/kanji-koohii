<?php
/**
 * Registry of the color themes available to users (see css/themes/ in the front-end).
 *
 * Single source for the layouts (data-theme attribute), the theme selection page,
 * and the validation of theme ids.
 *
 * Methods:
 *   sanitize(?string $id) : string
 *   getThemeColor(string $id) : string
 *
 * @author  Fabrice Denis
 */
class rtkThemes
{
  public const DEFAULT_THEME = 'classic';

  /**
   * Keyed by theme id (as stored in the database).
   *
   * - label:      name shown in the theme selection list
   * - themeColor: <meta name="theme-color">, and swatch in the theme selector list
   * - dark:       the theme is a dark color scheme
   */
  public const THEMES = [
    'classic' => [
      'label'      => 'Koohii Classic',
      'themeColor' => '#f0ddd4',
      'dark'       => false,
    ],
    'darkmode' => [
      'label'      => 'Koohii Dark',
      'themeColor' => '#1c1917',
      'dark'       => true,
    ],
  ];

  /**
   * Returns a valid theme id, the default theme if the value is null or unknown.
   */
  public static function sanitize(?string $id): string
  {
    return null !== $id && isset(self::THEMES[$id]) ? $id : self::DEFAULT_THEME;
  }

  public static function getThemeColor(string $id): string
  {
    return self::THEMES[self::sanitize($id)]['themeColor'];
  }
}
