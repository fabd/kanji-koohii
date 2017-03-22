<?php
/**
 * Miscellaneous helpers to plug some wanted php functions.
 * 
 * @author  Fabrice Denis
 */

class phpToolkit
{
  /**
   * Implement multi-byte aware ucfirst().
   * ucfirst() is not utf8-aware and can cause "Invalid multibyte sequence" down the line.
   * 
   * @uses  coreContext 'sf_charset'
   * 
   * @param object $string
   */
  public static function mb_ucfirst($string)
  {
    if (!function_exists('mb_ucfirst') && function_exists('mb_substr'))
    {
      $charset = sfConfig::get('sf_charset');
      $first = mb_substr($string, 0, 1, $charset);
      $string = mb_strtoupper($first, $charset) . mb_substr($string, 1, mb_strlen($string, $charset), $charset);
    }
    else
    {
      throw new Exception(__METHOD__.': no mb_substr() support.');
    }
    return $string;
  }

  /**
   * Extract a subset of an associative array by specifying the wanted keys.
   * 
   * If a wanted key does not exist in the input array, it is ignored.
   * 
   * @param array $input      Associative array
   * @param array $spliceKeys Indexed array of names of keys
   */
  public static function array_splice_keys(array $input, array $spliceKeys)
  {
    $result = array_intersect_key($input, array_flip($spliceKeys));
    /* is this slower or faster ?
    $result = array();
    foreach ($spliceKeys as $key)
    {
      if (array_key_exists($key, $input))
      {
        $result[$key] = $input[$key];
      }
    }
    */
    return $result;
  }

  /**
   * Extract a subset of values from an associative array by specifying the wanted keys.
   * 
   * If a wanted key does not exist in the input array, it is ignored.
   * 
   * @param array $input      Associative array
   * @param array $spliceKeys Indexed array of names of keys
   */
  public static function array_splice_values(array $input, array $spliceKeys)
  {
    $result = array_values(self::array_splice_keys($input, $spliceKeys));
    /*
    $result = array();
    foreach ($spliceKeys as $key)
    {
      if (array_key_exists($key, $input))
      {
        $result[] = $input[$key];
      }
    }
    */
    return $result;
  }

  /**
   * Merge class names given as strings or arrays (array arguments is faster).
   * 
   * @param  mixed  $classnames      Class name(s) given as a "class" attribute string, or an array of class names
   * @param  mixed  $add_classnames  Class names to add, given as string or array
   * @return string   String with all class names combined
   * 
   */
  public static function merge_class_names($classnames, $add_classnames)
  {
    if (is_string($classnames)) {
      $classnames = preg_split('/\s+/', $classnames);
    }
    
    if (is_string($add_classnames)) {
      $add_classnames = preg_split('/\s+/', $add_classnames);
    }
    
    if (count($add_classnames)) {
      $classnames = array_merge($classnames, $add_classnames);
    }

    return implode(' ', $classnames);
  }
}
