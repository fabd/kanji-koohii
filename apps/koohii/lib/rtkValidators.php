<?php
/**
 * Validators used throught the RevTK application.
 *
 * These should be used with request variables. Therefore even when validating
 * an integer, the value argument is always a string coming from GET/POST requests.
 *
 * Methods:
 *  validateUsername($value)
 *  validateNoHtmlTags($value)
 *  validateArrayKeys(array $input, array $valid_keys)
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
   * Validate options or parameters in an associative array against a set of
   * valid keys (case sensitive).
   *
   * @param   array   $input       Associate array of options/parameters
   * @param   array   $valid_keys  Indexed array of allowed keys
   *
   * @return  bool    true if all keys in $input are valid
   */
  public static function validateArrayKeys(array $input, array $valid_keys)
  {
    foreach ($input as $key => $value)
    {
      if (!in_array($key, $valid_keys))
      {
        return false;
      }
    }

    return true;
  }
}
