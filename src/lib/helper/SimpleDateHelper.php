<?php
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
function simple_format_date(int|string $date, string $format = 'd-m-Y')
{
  if (is_string($date)) {
    $date = strtotime($date);
    if ($date === false || $date === -1) {
      return 'x';
    }
  }

  return date($format, $date);
}
