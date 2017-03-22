<?php
/**
 * The coreValidator class helps with the repetitive task of validating
 * data by using a configuration file to define the validation rules.
 * 
 * The most common validators are provided by the base class, and custom
 * validators can be easily added with the CallbackValidator.
 * 
 * Data is provided as an associative array of keys and values. The array
 * usually comes from an action $request->getParameterHolder()->getAll()
 * to validate form data, but it could be data from an Ajax post, data
 * imported from a spreadsheet, etc.
 * 
 * 
 */

class coreValidator
{
  private
    $fields     = array(),
    $errors     = array();

  /**
   * Class constructor.
   * 
   * Loads the validator configuration file.
   * 
   */
  public function __construct($validatorName)
  {
    $configuration = $this->loadConfigurationFile($validatorName);
    $this->fields =  $configuration['fields'];
  }

  /**
   * Load a configuration file for this validator,
   * and do a basic check to verify its consistency.
   * 
   * @return  array   Associative array of fields and rules for validation.
   * @throws  sfException  If the file could not be loaded.
   */
  protected function loadConfigurationFile($validatorName)
  {
    $moduleName = sfContext::getInstance()->getModuleName();
    $file = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/validate/'.$validatorName.'.php';

    if (!is_readable($file))
    {
      throw new sfException(sprintf("Can not read validator file %s in module %s", $validatorName, $moduleName));
    }
    
    $configuration = require($file);

    if (!is_array($configuration) || !$this->hasParameter($configuration, 'fields'))
    {
      throw new sfException(sprintf("Invalid validator file %s in module %s", $validatorName, $moduleName));
    }
    
    return $configuration;
  }

  /**
   * Returns value for key in array, or default value.
   *
   */
  protected function getParameter($parameters, $name, $default = null)
  {
    return isset($parameters[$name]) ? $parameters[$name] : $default;
  }

  /**
   * Returns true if key exists in array.
   *
   */
  protected function hasParameter($parameters, $name)
  {
    return isset($parameters[$name]);
  }


  /**
   * Validate the data and returns true if data passed the validation rules.
   * 
   * @param  data    An associative array of data keys and values.
   * @return boolean True if data is valid, false if one or more validations failed. 
   */
  public function validate($data)
  {
    $this->errors = array();
    
    foreach($this->fields as $field => $rules)
    {
      $value = $this->getParameter($data, $field);

      // trim values before validation
      if (is_string($value))
      {
        $value = trim($value);
      }

      // if required, must be present and not empty
      if ($this->hasParameter($rules, 'required'))
      {
        // if it's required it can't be empty
        if ($value===null || strlen($value)<=0 || preg_match('/^\s+$/', $value))
        {
          $this->setError($field, $rules['required']['msg']);
          continue;
        }
      }
      elseif (preg_match('/^\s*$/', $value))
      {
        // if it's empty and not required, and not "notempty", don't validate any further
        /*
        if ($this->hasParameter($rules, 'notempty'))
        {
          $this->setError($field, $rules['notempty']['msg']);
          continue;
        }*/
        
        continue;
      }

      foreach ($rules as $ruleName => $ruleParams)
      {
        if ($ruleName == 'required')
        {
          continue;
        }
        
        // anything else than 'required' must be a named Validator
        if (!preg_match('/^\w+Validator$/', $ruleName))
        {
          throw new sfException("coreValidator rule '{$ruleName}' is not a Validator.");
        }
        
        // use builtin or custom validator
        if (method_exists($this, $ruleName))
        {
          // save value to compare to for CompareValidator
          if ($ruleName==='CompareValidator')
          {
            $compareField = $ruleParams['check'];
            $ruleParams['check_value'] = $this->getParameter($data, $compareField);
          }

          // validator returns true, or an error message
          $result = $this->$ruleName($value, $ruleParams);
          if ($result!==true)
          {
            if (is_null($result))
            {
              throw new sfException('Error message not set in validation file for '.$field);
            }
            $this->setError($field, $result);
          }
        }
        else
        {
          throw new sfException("Validator '{$ruleName}' not found in class ".get_class($this));
        }
      }
    }
    
    $valid = !$this->hasErrors();

    // Add error messages to the Request object
    if (!$valid)
    {
      sfContext::getInstance()->getRequest()->setErrors($this->errors);
    }
    
    return $valid;
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

  /**
   * Retrieves all errors.
   * 
   * @return array Associative array of name => message
   */
  public function getErrors()
  {
    return $this->errors;
  }
  
  /**
   * StringValidator
   * 
   * Optional Parameters:
   *    min
   *    min_error
   *    max
   *    max_error
   * 
   * @return mixed  true if validates, or a an error message as a string.
   */
  protected function StringValidator($value, $params)
  {
      $min = $this->getParameter($params, 'min');
      if ($min !== null && strlen(trim($value)) < $min)
      {
      // too short
      return $this->getParameter($params, 'min_error');
      }
  
      $max = $this->getParameter($params, 'max');
      if ($max !== null && strlen(trim($value)) > $max)
      {
      // too long
      return $this->getParameter($params, 'max_error');
      }

    return true;
  }

  /**
   * The NumberValidator verifies that the parameter is a number and allows you to apply
   * size constraints.
   * 
   * Optional Parameters:
   *
   * # <b>max</b>        - [none]                  - Maximum number size.
   * # <b>max_error</b>  - [Input is too large]    - An error message to use when
   *                                                 input is too large.
   * # <b>min</b>        - [none]                  - Minimum number size.
   * # <b>min_error</b>  - [Input is too small]    - An error message to use when
   *                                                 input is too small.
   * # <b>nan_error</b>  - [Input is not a number] - Default error message when
   *                                                 input is not a number.
   * # <b>type</b>       - [Any]                   - Type of number (Int, Integer, Decimal, Float).
   * # <b>type_error</b> - [Input is not a number] - An error message to use when
   *                                                 input is not a number.
   * 
   * @return mixed  True, or a string with an error message.
   */
  protected function NumberValidator($value, $params)
  {
    if (!preg_match('/^-?\d+(\.\d+)?$/', $value))
    {
      // it's NaN, what nerve!
      return $this->getParameter($params, 'nan_error');
    }

    $type = strtolower($this->getParameter($params, 'type'));

    switch ($type)
    {
      case "decimal":
      case "float":
      {
        if (substr_count($value, '.') != 1)
        {
          // value isn't a float, shazbot!
          return $this->getParameter($params, 'type_error');
        }

        // cast our value to a float
        $value = (float) $value;

        break;
      }

      case "int":
      case "integer":
      {
        // Note: (Both 3 AND 3.0 are BOTH considered integers and 3.1 is not)
        if ((float) $value != (int) $value)
        {
          // is not an integer.
          return $this->getParameter($params, 'type_error');
        }

        // cast our value to an integer
        $value = (int) $value;

        break;
      }

    }

    $min = $this->getParameter($params, 'min');

    if ($min !== null && $value < $min)
    {
      // too small
      return $this->getParameter($params, 'min_error');
    }

    $max = $this->getParameter($params, 'max');

    if ($max !== null && $value > $max)
    {
      // too large
      return $this->getParameter($params, 'max_error');
    }

    return true;
  }

  /**
   * Regular Expression validator.
   * 
   * Required Parameters:
   *    match:      true,
   *    pattern:      "/^[a-zA-Z ]+$/",
   *    match_error:  "Only letters and spaces!"
   * 
   * @return mixed  True, or a string with an error message.
   */
  protected function RegexValidator($value, $params)
  {
      $match   = $this->getParameter($params, 'match');
    $pattern = $this->getParameter($params, 'pattern');
    
    if (($match && !preg_match($pattern, $value)) ||
      (!$match && preg_match($pattern, $value)))
    {
      return $this->getParameter($params, 'match_error');
    }
    
    return true;
  }
  
  /**
   * The EmailValidator verifies that the parameter contains a value that qualifies as an
   * email address.
   * 
   * Required Parameters:
   *    email_error:  "Please enter a valid email address."
   *  
   * Optional Parameters:
   *    strict:       "true" (default) to match only emails in the form name@domain.extension
   *                  "false" to match emails against RFC822 (this will accept emails
   *                   such as me@localhost)
   *                   
   * @return mixed  True, or a string with an error message.
   */
  protected function EmailValidator($value, $params)
  {
    $strict = $this->getParameter($params, 'strict', true);
    if ($strict == true)
    {
      $re = '/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i';
    }
    else
    {
      /* Cal Henderson: http://iamcal.com/publish/articles/php/parsing_email/pdf/
       * The long regular expression below is made by the following code
       * fragment:
       *
       *   $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
       *   $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
       *   $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'
       *         . '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
       *   $quoted_pair = '\\x5c\\x00-\\x7f';
       *   $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
       *   $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
       *   $domain_ref = $atom;
       *   $sub_domain = "($domain_ref|$domain_literal)";
       *   $word = "($atom|$quoted_string)";
       *   $domain = "$sub_domain(\\x2e$sub_domain)*";
       *   $local_part = "$word(\\x2e$word)*";
       *   $addr_spec = "$local_part\\x40$domain";
       */

      $re = '/^([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-'
           .'\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80-\\xff]|\\x5c\\x00-'
           .'\\x7f)*\\x22)(\\x2e([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-'
           .'\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80'
           .'-\\xff]|\\x5c\\x00-\\x7f)*\\x22))*\\x40([^\\x00-\\x20\\x22\\x28\\x29'
           .'\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x5b([^'
           .'\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*\\x5d)(\\x2e([^\\x00-'
           .'\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-'
           .'\\xff]+|\\x5b([^\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*'
           .'\\x5d))*$/'
      ;
    }

    if (!preg_match($re, $value))
    {
      $error = $this->getParameter($params, 'email_error');
      return $error;
    }

    $checkDomain = $this->getParameter($params, 'check_domain');
    if ($checkDomain && function_exists('checkdnsrr'))
    {
      $tokens = explode('@', $value);
      if (!checkdnsrr($tokens[1], 'MX') && !checkdnsrr($tokens[1], 'A'))
      {
        $error = $this->getParameter($params, 'email_error');

        return $error;
      }
    }
    
    return true;
  }

  /**
   * The UrlValidator verifies that the value qualifies as a valid URL.
   * 
   * Required Parameters:
   *    url_error:    Error message for failed validation.
   * 
   * @return mixed  True, or a string with an error message.
   */
  protected function UrlValidator($value, $params)
  {
    $re = '/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';

    if (!preg_match($re, $value))
    {
      $error = $this->getParameter($params, 'url_error');
      return $error;
    }

    return true;
  }

  /**
   * The CompareValidator compares two different request parameters.
   * It is very useful for password checks.
   * 
   * Required Parameters:
   *    check:      Name field to compare to
   *    compare_error: "Fields don't match."
   *
   * @return mixed  True, or a string with an error message.
   */  
  protected function CompareValidator($value, $params)
  {
      $check_value = $this->getParameter($params, 'check_value');

    if ($value != $check_value)
    {
      $error = $this->getParameter($params, 'compare_error');
      return $error;
    }

    return true;
  }

  /**
   * The CallbackValidator allows you to use a custom callback function or method to
    * validate the input. The function should return true on valid and false on invalid
    * and should be callable using call_user_func().
    * 
    * Required Parameters:
    *    callback:       A valid callback function or Class::method. When using class/method
    *                    specify it as an array in the config file as array('class', 'method')
    *    invalid_error:  Error message for failed validation.
   * 
   * @return mixed  True, or a string with an error message.
   */
  protected function CallbackValidator($value, $params)
  {
    $callback = $this->getParameter($params, 'callback');
    
    if (!call_user_func($callback, $value))
    {
      $error = $this->getParameter($params, 'invalid_error');
      return $error;
    }
    
    return true;
  }
}
