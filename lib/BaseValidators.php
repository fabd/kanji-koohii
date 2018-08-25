<?php
/**
 * Helpers for validation of request parameters.
 *
 * The validate functions always return a boolean.
 *
 * The sanitize functions extend the related validate function,
 * and always return a value of the expected type and format, or throw
 * an exception. These are meant to be used in actions.
 * 
 * @author     Fabrice Denis
 */

class BaseValidators
{
  public static function validateNotEmpty($value)
  {
    return !empty($value) || ($value!=='0' && $value!==0);
  }

  /**
   *
   * @return bool    Returns TRUE if $value is an integer or if the string is only 0-9 digits.
   */
  public static function validateInteger($value)
  {
    $value = (string)$value;
    return ctype_digit($value);
  }

  public static function validateNoHtmlTags($value)
  {
    return (strip_tags($value) == $value);
  }

  /**
   * Checks that the string doesn't use 4 byte characters (ie. for mysql's broken "utf8" charset).
   *
   * @return  bool    true if string fits in mysql "utf8"
   */
  public static function validateMysqlUtf8($value)
  {
    return (preg_match('/[\x{10000}-\x{10FFFF}]/u', $value) === 0);
  }

  /**
   * Returns a positive integer, or throws an exception.
   *
   * @return int
   */
  public static function sanitizeInteger($value)
  {
    // must be all decimal digits
    if (!BaseValidators::validateInteger($value))
    {
      throw new sfException(__METHOD__);
    }

    return intval($value);
  }
}
