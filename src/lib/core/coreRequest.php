<?php
/**
 * Extends sfWebRequest with utilities.
 *
 * Misc:
 *
 *   getContentJson()
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
 *
 */

class coreRequest extends sfWebRequest
{
  protected
    $errors  = [];

  /**
   * Extends getContent() and returns an object with decoded JSON.
   *
   * Since we fully expect JSON, throw errors otherwise. 
   * 
   * @return object
   */
  public function getContentJson()
  {
    if ($this->getContentType() !== 'application/json') {
      throw new sfException(sprintf('Content-type is not expected "application/json".'));
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
      }
      elseif ($val === 'false') {
        $val = false;
      }
      elseif ($val === 'true') {
        $val = true;
      }
    }

    $obj = (object) $params;

    return $obj;
  }

  /**
   * Retrieves an error message.
   * 
   * @param string Key
   * 
   * @return string An error message or null if the error doesn't exist
   */
  public function getError($name)
  {
    return isset($this->errors[$name]) ? $this->errors[$name] : null;
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
   * @param string An error name
   *
   * @return string An error message, if the error was removed, otherwise null
   */
  public function removeError($name)
  {
    $retval = null;
    if (isset($this->errors[$name]))
    {
        $retval = $this->errors[$name];
        unset($this->errors[$name]);
    }

    return $retval;
  }

  /**
   * Sets an error message.
   * 
   * @param string Key
   * @param string Error message
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
   * @param array An associative array of errors and their associated messages
   */
  public function setErrors($errors)
  {
    $this->errors = array_merge($this->errors, $errors);
  }
  
  /**
   * Checks whether or not any errors exists.
   * 
   * @return boolean True if any error exists, false otherwise.
   */
  public function hasErrors()
  {
    return count($this->errors) > 0;
  }
  
  /**
   * Checks if an error exists for given key.
   * 
   * @return boolean True if an error exists, false otherwise.
   */
  public function hasError($name)
  {
    return array_key_exists($name, $this->errors);
  }
}
