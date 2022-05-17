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
}
