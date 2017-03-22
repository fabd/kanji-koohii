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
  /**
   * Value can not be empty.
   * 
   */
  public static function validateNotEmpty($value)
  {
    return !empty($value) || ($value!=='0' && $value!==0);
  }

  /**
   * Checks that every character is a digit (must be a positive integer).
   * 
   * @param  mixed   $value
   *
   * @return bool    Returns TRUE if $value is an integer or if the string is only 0-9 digits.
   */
  public static function validateInteger($value)
  {
    $value = (string)$value;
    return ctype_digit($value);
  }

  /**
   * Returns a positive integer, or throws an exception.
   * 
   * @param  mixed  $value
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
