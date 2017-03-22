<?php
/**
 * This class handles error reporting in development and production modes.
 * 
 * Also includes debugging output functions.
 * 
 * @author  Fabrice Denis
 * 
 */

class coreError
{
  private static $devmode = false;
  private static $errMask = 0;

  public static function initialize($devmode)
  {
    self::$devmode = $devmode;
    
    // handle php5.3.0 compatibility: pretend E_DEPRECATED exists
    if (!defined('E_DEPRECATED'))
    {
      // use the PHP 5.3.0 value so it doesn't mix with existing bits 
      define('E_DEPRECATED', 8192);
    }

    self::setupAssertions();
    self::setupErrorHandler();

    // error settings
    ini_set('display_errors', self::$devmode ? 'on' : 'off');
    //error_reporting(...);
  }

  public static function setupAssertions()
  {
    assert_options(ASSERT_WARNING, 0);
    assert_options(ASSERT_BAIL, 0);

    if (self::$devmode)
    {
      assert_options(ASSERT_QUIET_EVAL, 0);
      assert_options(ASSERT_ACTIVE, 1);
      assert_options(ASSERT_CALLBACK, 'coreError::devAssertHandler');
    }
    else
    {
      assert_options(ASSERT_QUIET_EVAL, 1); // this could fail silently!
      assert_options(ASSERT_ACTIVE, 1);
      assert_options(ASSERT_CALLBACK, 'coreError::prodAssertHandler');
    }
  }

  /**
   * The development assert handler gives all details.
   * 
   * @see assert_options()
   */
  public static function devAssertHandler($file, $line, $code) 
  {
    throw new sfException("Assertion failed in {$file} at line {$line} with code : {$code}");
  }

  /**
   * The production assert handler does not show sensible information.
   * 
   * @see assert_options()
   */
  public static function prodAssertHandler($file, $line, $code) 
  {
    echo <<<EOD
<div style="padding:3px 5px;background:#ffa020;font:12px Georgia;color:#4b2b00;">
  Assertion failed at line <b>{$line}</b></pre>
</div>
EOD;
    exit();
  }

  public static function setupErrorHandler()
  {
    // note these are only valid for the default php error handler
    if (self::$devmode) {
      // Show all errors, warnings and notices including coding standards (see php.ini).
      self::$errMask = E_ALL | E_STRICT | E_DEPRECATED;
      error_reporting(self::$errMask);
    }
    else {
      self::$errMask = E_ALL & ~E_NOTICE & ~E_USER_WARNING & ~E_USER_NOTICE;
      error_reporting(self::$errMask);
    }
    
    set_error_handler('coreError::errorHandler');
  }

  public static function errorHandler($errno, $errstr, $errfile, $errline)
  {
    if (!($errno & self::$errMask)) {
      return false;
    }

    $bg = '#CCC';
    switch ($errno)
    {
      case E_USER_ERROR:
        $type = 'USER ERROR'; $bg = '#ff3820'; $msgstyle = 'background:#ffc8c2;';
        break;
      case E_USER_WARNING:
        $type = 'USER WARNING'; $bg = '#ffa020'; $msgstyle = 'background:#ffd9a6;';
        break;
      case E_USER_NOTICE:
        $type = 'USER NOTICE'; $bg = '#fce339'; $msgstyle = 'background:#fff4b3;';
        break;
      case E_NOTICE:
        $type = 'NOTICE'; $bg = '#fce339'; $msgstyle = 'background:#fff4b3;';
        return false;
      case E_DEPRECATED:
        $type = 'DEPRECATED'; $bg = '#fce339'; $msgstyle = 'background:#fff4b3;';
        break;
      default:
        $type = 'PHP ERROR'; $bg = 'red'; $msgstyle = 'background:#ffc8c2;';
        break;
    }
  
    // display error box, but allow to continue
    if ($errno & (E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED))
    {
      echo <<<EOD
<div style="-moz-border-radius:2px;border:4px solid {$bg};background:{$bg};padding:0;margin:0 0 1px;font:12px/1.2em monospace, sans-serif;color:#000;">
  $type in file <b>{$errfile}</b> at line <b>{$errline}</b>
  <div style="padding:2px 5px;{$msgstyle}">
    $errstr
  </div>
</div>
EOD;
      return true;
    }

    throw new sfException('Error: '.$errstr);

    // all other errors halt the script
    die();
      return false;
  }
}
