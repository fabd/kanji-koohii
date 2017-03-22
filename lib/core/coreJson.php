<?php
/**
 * coreJson uses the native functions if present, otherwise it uses the PEAR Json library
 * which is very forgiving, but also slower.
 *  
 * If it is suspected there is a problem with decoding a JSON string, the native functions
 * can be disabled by setting the USE_NATIVE constant to false.
 * 
 * Decoding caveats of the native implementation:
 * 
 *   ""             Empty string
 *   "1"            int(1)
 *   true           bool(true)
 *   "true"         bool(true)
 *   TRUE           Error, use lowercase
 *   null           NULL
 *   NULL           Error, use lowercase
 *   .5             Error, use 0.5
 *   0xFF           Error, hexadecimal values apparently not supported
 *   [1,]           Error, cannot skip values (same for [,1] [1,,3] etc)
 *   \r\n           Error, use double backslash \\r will be \r in string then the ascii value after decoding
 *   
 * Javascript comments (slash slash, slash star) don't work in the native decoding !
 * 
 * 
 * @author  Fabrice Denis
 * @see     http://www.json.org/
 */

class coreJson
{
  /**
   * Option to pass to decode() to get associative arrays instead of objects.
   */
  const JSON_ASSOC = 1;

  /**
   * Set this to true to use the native php json encoding & decoding.
   */
  const USE_NATIVE = false;
  
  /**
   * Check if the native json encoding/decoding functions are present.
   * 
   * @return bool   Returns true if native functions are available & enabled (see USE_NATIVE).
   */
  public static function checkNative()
  {
    static $native = null;
    static $loaded = false;

    if (self::USE_NATIVE) {
      if ($native===null) {
        if ($native = function_exists('json_decode')) {
          return true;
        }
      }
      return true;
    }

    if (!$loaded)
    {
      require_once(dirname(__FILE__).'/lib/Services_JSON.php');
      $loaded = true;
    }

    return false;
  }
  
  
  /**
   * Encode data to a JSON string.
   * 
   * Strings must be ascii or utf8.
   * 
   * @param  mixed   Any number, boolean, string, array, or object
   * @param  int     php json_encode() options (ie. JSON_PRETTY_PRINT)
   * 
   * @return string  Json format string.
   */
  public static function encode($object, $php_json_options = 0)
  {
    if (self::checkNative()) {
      $json_string = json_encode($object, $php_json_options);
    }
    else {
      $json = new Services_JSON();
      $json_string = $json->encode($object);
    }
    return $json_string;
  }

  /**
   * Decodes JSON string to an object.
   * 
   * @param  string  Json string to be decoded
   * @param  integer Use option JSON_ASSOC to get associative arrays instead of objects
   * @return mixed   Objects or associative arrays
   */  
  public static function decode($json_string, $options = 0)
  {
    if (self::checkNative()) {
      $result = json_decode($json_string, $options===self::JSON_ASSOC);
      if (!is_array($result) && !is_object($result)) {
        throw new sfException('coreJson decode error');
      }
    }
    else {
      $json = new Services_JSON($options===self::JSON_ASSOC ? SERVICES_JSON_LOOSE_TYPE : 0);
      $result = $json->decode($json_string);
    }
    return $result;
  }
}
