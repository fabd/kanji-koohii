<?php
/**
 * Handy functions for debugging output and raising user errors.
 * 
 * The first 3 functions correspond to php USER level errors:
 * 
 *   error()     User error, and ends running script.
 *   warn()      User warning
 *   out()       User notice, sprintf style arguments
 *
 *   printr()    Debug the contents of a variable, prints output of print_r inside a PRE tag.
 * 
 * 
 * Debugging autoloading:
 * 
 *   files()     Outputs PHP's get_included_files() (see what is autoloaded)
 * 
 * 
 * Debugging requests:
 * 
 *   cookies()
 *   user()
 *   request()
 * 
 * 
 * @author  Fabrice Denis
 */

class DBG
{
  const DBG_PRINTR = 1;

  private static function begin($status = 0)
  {
    switch ($status) {
      case self::DBG_PRINTR: [$bg, $fg] = ['#208020', '#fff']; break;
      default: [$bg, $fg] = ['#808080', '#fff']; break;
    }
    echo <<<HTML
      <div class="dbg" style="
        padding:4px 8px; border-radius: 4px;
        background:{$bg}; color:{$fg};
        font:14px 'Courier New', sans-serif;
      ">
      HTML;
  }
  
  private static function end()
  {
    echo "</div>\n";
  }

  //   Debug message and die()
  public static function error($msg)
  {
    trigger_error($msg, E_USER_ERROR);
  }

  public static function files()
  {
    self::printr(get_included_files());
  }
  
  //  Debug output and continue
  public static function out()
  {
    $arguments  = func_get_args();
    $message = func_num_args() > 1 ? call_user_func_array('sprintf', $arguments) : $arguments[0];
    trigger_error($message, E_USER_NOTICE);
  }
  
  //  Debug output and continue
  public static function warn($msg)
  {
    self::begin();
    trigger_error($msg, E_USER_WARNING);
    self::end();
  }

  //  Inspect a variable contents
  public static function printr($expr)
  {
    self::Begin(self::DBG_PRINTR);
    echo '<strong>'.gettype($expr).'</strong> =<br /><pre>';
    if (is_bool($expr)) {
      echo $expr===true ? 'true' : 'false';
    }
    elseif (is_null($expr)) {
      echo 'null';
    }
    elseif (is_string($expr)) {
      echo '"'.$expr.'"';
    }
    elseif (is_object($expr) || is_array($expr)) {
      echo var_dump($expr);
    }
    else {
      echo $expr;
    }
    echo '</pre>';
    self::End();
  }
  
  public static function cookies()
  {
    echo '<pre class="info">';
    print_r($_COOKIE);
    echo '</pre>';
  }

  public static function user()
  {
    echo '<pre class="info">';

    if (sfContext::getInstance()->has('user'))
    {
      $user = sfContext::getInstance()->getUser();
    
        $info = [
        'attributes'  => $user->getAttributeHolder()->getAll(),
        'credentials' => $user->getCredentials()
      ];
    
      print_r($info);
    }
    else
    {
      echo 'User not available.';
    }
    echo '</pre>';
  }

  /**
   * Output the Request parameters for debugging.
   * 
   */
  public static function request()
  {
    $request = sfContext::getInstance()->getRequest();
    echo '<pre>'.print_r($request->getParameterHolder()->getAll(), true).'</pre>';
  }
}
