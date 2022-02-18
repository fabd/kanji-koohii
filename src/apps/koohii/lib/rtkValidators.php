<?php
/**
 * Validators used throught the RevTK application.
 *
 * These should be used with request variables. Therefore even when validating
 * an integer, the value argument is always a string coming from GET/POST requests.
 *
 * Validate methods:
 *   
 *   validateUsername($value)
 *   validateNoHtmlTags($value)
 *
 * Sanitize methods (validate & return value in the expected type or throws exception):
 *   
 *   sanitizeCJKUnifiedUCS($value)
 * 
 * 
 * @author     Fabrice Denis
 */

class rtkValidators
{
  /**
   * Validate RevTK username.
   * 
   * @return 
   * @param object $value
   * @param object $params
   */
  public static function validateUsername($value)
  {
    // no special characters
    $valid = (preg_match('/^[a-zA-Z0-9_]+$/', $value) > 0);
    
    // no leet prefix, suffix or multiple non-alphanumeric characters
    $valid = $valid && (preg_match('/^[0-9_]|_$|__/', $value) === 0);

    return $valid;
  }

  public static function validateUserLocation($value)
  {
    return BaseValidators::validateNoHtmlTags($value) &&
           BaseValidators::validateMysqlUtf8($value);
  }

  /**
   * Cast value to an int, and makes sure it checks against supported CJK range.
   *
   * @return int
   */
  public static function sanitizeCJKUnifiedUCS($value)
  {
    $ucs_code = BaseValidators::sanitizeInteger($value);

    if (!CJK::isCJKUnifiedUCS($ucs_code)) {
      throw new sfException(__METHOD__);
    }

    return $ucs_code;
  }
}
