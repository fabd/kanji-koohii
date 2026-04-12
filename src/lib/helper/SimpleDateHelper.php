<?php
/**
 * Helpers to output dates in templates.
 *
 * @author  Fabrice Denis
 *
 * @param mixed      $date
 * @param mixed      $format
 * @param mixed|null $culture
 * @param mixed|null $charset
 */

/**
 * Simpler version of symfony's DateHelper that ignores the
 * culture and charset arguments. We also use php date() format directly,
 * but this should be easier to upgrade if we move on to the international
 * date support of Symfony.
 *
 * The default date format is in the form '23/01/2008'.
 *
 * @return string formatted date time, or empty string if conversion error
 */
function simple_format_date($date, $format = 'd-m-Y', $culture = null, $charset = null)
{
  if (is_null($date)) {
    return '';
  }

  if (is_string($date)) {
    $date = strtotime($date);
    if ($date === false || $date === -1) {
      return 'x';
    }
  }

  $s_date = date($format, $date);

  return is_string($s_date) ? $s_date : '';
}
