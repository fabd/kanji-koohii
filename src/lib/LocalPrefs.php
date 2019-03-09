<?php
/**
 * LocalPrefs is a wrapper to manage session-duration preferences.
 * 
 * Methods:
 *   set($name, $value)                       Proxy to rtkUser attributes
 *   get($name, $default = null)
 *   
 *   sync($name, $value, $default = null)
 *   syncRequestParams($prefix, array $params)
 * 
 * 
 * @author     Fabrice Denis
 */

class LocalPrefs
{
  protected
    $user    = null;
  
  /**
   * 
   * @return 
   */
  public function __construct($user)
  {
    $this->user = $user;
  }
  
  public function set($name, $value)
  {
    $this->user->setAttribute($name, $value);
  }

  public function get($name, $default = null)
  {
    return $this->user->getAttribute($name, $default);
  }

  /**
   * Updates local preference and returns the new value.
   * 
   * If value is set, update local pref, return that value.
   * If value is not set, and local pref exists, return current local pref.
   * If local pref doesn't exist, value is not set, and default is provided, set and return that.
   * Otherwise return null.
   * 
   * @param  string  $name     Name for user attribute (session).
   * @param  mixed   $value    Value for local pref, or null (typically from request->getParameter)
   * @param  mixed   $default  Default value if local pref and value are not set
   * 
   * @return mixed   Current value
   */
  public function sync($name, $value, $default = null)
  {
    $current = $this->get($name);

    if (is_null($value))
    {
      if (is_null($current))
      {
        if (!is_null($default))
        {
          $this->set($name, $default);
          return $default;
        }
      }
      else
      {
        return $current;
      }
    }
    else
    {
      if ($value !== $current)
      {
        $this->set($name, $value);
      }
      return $value;
    }
    
    return null;
  }
  
  /**
   * Update local preferences from request parameters and returns current values.
   * 
   * This method is handy to remember user interaction with data tables and other
   * components that send parameters on the query string. Use $prefix to uniquely
   * identify the page to which the request params apply.
   * 
   * Example:
   *   Remember the state of a data table on page "client_report":
   *   syncRequestParams('client_report', array(
   *     'sortcol' => 'clientname',
   *     'rows'    => 20
   *   )
   *   
   *   This will store the local prefs (user attributes):
   *   
   *   client_report.sortcol
   *   client_report.rows
   *   
   *   It will return a hash with the updated values:
   *   
   *   array( 'sortcol' => ..., 'rows' => ... )
   * 
   * 
   * @param  string  $prefix  Prefix for the local preference name of each param
   * @param  array   $params  Default values for parameters (hash)
   * 
   * @return array   A hash of parameters (without prefix) and their updated values
   */
  public function syncRequestParams($prefix, array $params)
  {
    $request = sfContext::getInstance()->getRequest();
    $values = array();
    foreach ($params as $name => $default)
    {
      $values[$name] = $this->sync($prefix.'.'.$name, $request->getParameter($name), $default);
    }
    return $values;
  }
}
