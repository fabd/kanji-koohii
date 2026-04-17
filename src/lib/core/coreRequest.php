<?php
/**
 * Extends sfWebRequest with utilities.
 *
 * Misc:
 *
 *   getContentJson()
 *   getParamsAsJson()
 *
 *
 * The old symfony error handling that seems to have been refactored into the sfForms:
 *
 *   getError($name)
 *   getErrors()
 *   hasError($name)
 *   hasErrors()
 *   removeError($name)
 *   setError($name, $message)
 *   setErrors($errors)
 */
class coreRequest extends sfWebRequest
{
  protected $errors = [];

  /**
   * Extends getContent() and returns an object with decoded JSON.
   *
   * Since we fully expect JSON, throw errors otherwise.
   *
   * @return object
   *
   * @throws sfException
   */
  public function getContentJson()
  {
    if ($this->getContentType() !== 'application/json') {
      throw new sfException('Content-type is not expected "application/json".');
    }

    $data = json_decode($this->getContent(), false);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new sfException(sprintf("json_decode() error: '%s'", json_last_error_msg()));
    }

    return $data;
  }

  /**
   * This helper makes a GET request with url parameters look like a json POST.
   *
   * NOTE!
   *   "true", "false" and integer values are type cast!
   *
   * @return object
   */
  public function getParamsAsJson()
  {
    $params = $this->getParameterHolder()->getAll();

    // remove symfony bits
    unset($params['action'], $params['module']);

    // very basic type casting
    foreach ($params as &$val) {
      if (ctype_digit($val)) {
        $val = (int) $val;
      } elseif ($val === 'false') {
        $val = false;
      } elseif ($val === 'true') {
        $val = true;
      }
    }

    $obj = (object) $params;

    return $obj;
  }

  /**
   * Retrieves an error message.
   *
   * @param string $name Key
   *
   * @return string|null An error message or null if the error doesn't exist
   */
  public function getError($name)
  {
    return $this->errors[$name] ?? null;
  }

  /**
   * Retrieves all errors for this request.
   *
   * @return array Associative array of name => message
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Removes an error.
   *
   * @param string $name An error name
   *
   * @return string|null An error message, if the error was removed, otherwise null
   */
  public function removeError($name)
  {
    $retval = null;
    if (isset($this->errors[$name])) {
      $retval = $this->errors[$name];
      unset($this->errors[$name]);
    }

    return $retval;
  }

  /**
   * Sets an error message.
   *
   * @param string $name    Key
   * @param string $message Error message
   */
  public function setError($name, $message)
  {
    $this->errors[$name] = $message;
  }

  /**
   * Sets an array of errors.
   *
   * If an existing error name matches any of the keys in the supplied
   * array, the associated message will be overridden.
   *
   * @param array<string, string> $errors An associative array of errors and their associated messages
   */
  public function setErrors(array $errors)
  {
    $this->errors = array_merge($this->errors, $errors);
  }

  /**
   * Checks whether or not any errors exists.
   *
   * @return bool true if any error exists, false otherwise
   */
  public function hasErrors()
  {
    return count($this->errors) > 0;
  }

  /**
   * Checks if an error exists for given key.
   *
   * @param mixed $name
   *
   * @return bool true if an error exists, false otherwise
   */
  public function hasError($name)
  {
    return array_key_exists($name, $this->errors);
  }

  /**
   * Retrieves a parameter for the current request (fixed for PHPStan).
   *
   * @param string $name    Parameter name
   * @param ?string $default Parameter default value
   */
  public function getParameter($name, $default = null): ?string
  {
    return $this->parameterHolder->get($name, $default);
  }
}
