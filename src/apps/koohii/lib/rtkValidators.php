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
 * @author     Fabrice Denis
 */
class rtkValidators
{
  /**
   * Validate RevTK username.
   */
  public static function validateUsername(string $value): bool
  {
    // no special characters
    $valid = (preg_match('/^[a-zA-Z0-9_]+$/', $value) > 0);

    // no leet prefix, suffix or multiple non-alphanumeric characters
    $valid = $valid && (preg_match('/^[0-9_]|_$|__/', $value) === 0);

    return $valid;
  }

  public static function validateUserLocation(string $value): bool
  {
    return BaseValidators::validateNoHtmlTags($value)
           && BaseValidators::validateMysqlUtf8($value);
  }

  /**
   * Always returns a bool. Best used for checking boolean request parameters,
   * to accept 'truthy' values. Less prone to break when updating forms.
   *
   * @param mixed $value
   *
   * @return bool
   */
  public static function sanitizeBool($value)
  {
    return
      true      === $value
      || 'true' === $value
      || 0 !== intval($value);
  }

  /**
   * Cast value to an int, and makes sure it checks against supported CJK range.
   * 
   * @throws sfException
   */
  public static function sanitizeCJKUnifiedUCS(int|string $value): int
  {
    $ucs_code = BaseValidators::sanitizeInteger($value);

    if (!CJK::isCJKUnifiedUCS($ucs_code)) {
      throw new sfException(__METHOD__);
    }

    return $ucs_code;
  }
}
