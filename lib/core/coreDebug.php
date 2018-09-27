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
 * The next 3 are handy for debugging http requests:
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
  const DBG_PHPDATA = 2;
  const DBG_PHPDATA2 = 3;

  private static function begin($status = 0)
  {
    switch ($status) {
      case self::DBG_PRINTR: $bg = '#208020'; $fg = 'white'; break;
      case self::DBG_PHPDATA:  $bg = '#204080'; $fg = 'white'; break;
      case self::DBG_PHPDATA2: $bg = '#305090'; $fg = 'white'; break;
      default: $bg = '#808080'; $fg = 'white'; break;
    }
    echo "<div class=\"dbg\" style=\"margin:0; padding:2px 5px; border:1px solid $bg; background:$bg; font:12px Arial; color:$fg;\">\n";
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
    trigger_error($msg, E_USER_WARNING);
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
    
        $info = array(
        'attributes'  => $user->getAttributeHolder()->getAll(),
        'credentials' => $user->listCredentials()
      );
    
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
