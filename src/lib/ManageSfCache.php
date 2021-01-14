<?php
/**
 * ManageSfCache provides utility methods to manage Symfony's cache of
 * actions and partials.
 *
 * CLEARING CACHED TEMPLATES
 *
 *   $path = ManageSfCache::getRealPathForCache('module/action');
 *   ManageSfCache::recursiveDeleteFromPath($path);
 *   
 */

class ManageSfCache
{
  const APP_NAME = 'koohii';

  /**
   * Clear several cache parts at once using wildcard cache key (sf 1.1).
   *
   * Remember: for partials and components, action is "_PartialName".
   * 
   */
  public static function clearCacheWildcard($module, $action)
  {
    $cacheManager = sfContext::getInstance()->getViewCacheManager();
    $cacheManager->remove("@sf_cache_partial?module={$module}&action={$action}&sf_cache_key=*");
  }

  /**
   * Return the root path used by symfony for all cached templates of action or _partial
   * 
   */
  public static function getRealPathForCache($route)
  {

    list($module, $action) = self::getModuleActionFromRoute($route);

    $dir = sfConfig::get('sf_cache_dir').'/'.self::getAppSlashEnv().'/template/'.self::getHostName().'/all/';
    
    if (substr($action, 0 , 1) === '_') {
      $dir = $dir.'sf_cache_partial/'.$module.'/_'.$action.'/sf_cache_key/';
    }
    else {
      $dir = $dir.$module.'/'.$action.'/';
    }

  // dbg::out($dir);

    // real path or false
    return realpath($dir);
  }

  // delete all files and subfolders in path (but not the path itself)
  public static function recursiveDeleteFromPath($path, $level = 0)
  {
  //  echo "<br>$path --- level $level";
    if (is_dir($path) === true)
    {
      $files = array_diff(scandir($path), ['.', '..']);

      foreach ($files as $file)
      {
        self::recursiveDeleteFromPath(realpath($path) . '/' . $file, $level + 1);
      }

      if ($level > 0) {
        return rmdir($path);
      }
      else {
        return true;
      }
    }
    else if (is_file($path) === true)
    {
      return unlink($path);
    }

    return false;
  }

  // return file count and total size for all files (recursively) in cache directory for module/action (or module/_partial)
  public static function getCacheSizeInfo($route)
  {
    $real_path = ManageSfCache::getRealPathForCache($route);

    if (false === $real_path) {
      return ['real_path' => '-INVALID PATH-', 'file_count' => -1, 'size_kb' => -1];
    }

    $size = 0;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real_path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $i) {
      $size += $i->getSize();
    }

    //echo sprintf('%d size, %d files', $size, iterator_count($iterator));

    return [
      'real_path'   => $real_path,
      'file_count'  => iterator_count($iterator),
      'size_kb'     => (int) ($size / 1024),
    ];
  }

  // returns string 'application/environment' (part of the cache folder path)
  public static function getAppSlashEnv()
  {
    $env = strtolower(sfApplicationConfiguration::getActive()->getEnvironment());
    return self::APP_NAME.'/'.$env;
  }

  // returns array('module', 'action') for given $route
  public static function getModuleActionFromRoute($route)
  {
    list($module, $action) = explode('/', $route);
    return [$module, $action];
  }

  // Format host name as used by Symfony in cache path name
  public static function getHostName()
  {
    $context  = sfContext::getInstance();
    $hostName = $context->getRequest()->getHost();

    $hostName = preg_replace('/[^a-z0-9\*]/i', '_', $hostName);
    $hostName = preg_replace('/_+/', '_', $hostName);

    return strtolower($hostName);
  }
}
