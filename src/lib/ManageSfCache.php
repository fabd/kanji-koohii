<?php
/**
 * ManageSfCache provides utility methods to manage Symfony's cache of
 * actions and partials.
 *
 * Static methods:
 *
 *   clearCacheWildcard()      ... clear all cache for given action/module
 *
 *
 * CLEARING CACHED TEMPLATES
 *
 *   $path = ManageSfCache::getRealPathForCache('module/action');
 *   ManageSfCache::recursiveDeleteFromPath($path);
 */
class ManageSfCache
{
  public const APP_NAME = 'koohii';

  /**
   * Clear several cache parts at once using wildcard cache key (sf 1.1).
   *
   * Remember: for partials and components, action is "_PartialName".
   *
   * @param mixed $module
   * @param mixed $action
   */
  public static function clearCacheWildcard($module, $action)
  {
    $cacheManager = sfContext::getInstance()->getViewCacheManager();
    $cacheManager->remove("@sf_cache_partial?module={$module}&action={$action}&sf_cache_key=*");
  }

  /**
   * Return the root path used by symfony for all cached templates of action or _partial.
   *
   * @return false|string
   */
  public static function getRealPathForCache(string $route)
  {
    [$module, $action] = self::getModuleActionFromRoute($route);
    // LOG::info("module action = $module $action");

    $dir = sfConfig::get('sf_cache_dir').'/'.self::getAppSlashEnv().'/template/'.self::getHostName().'/all/';

    if (substr($action, 0, 1) === '_')
    {
      $dir = $dir.'sf_cache_partial/'.$module.'/_'.$action.'/sf_cache_key/';
    }
    else
    {
      $dir = $dir.$module.'/'.$action;

      if (is_file("{$dir}.cache"))
      {
        $dir = "{$dir}.cache"; // simple action with no params
      }
      else
      {
        $dir = $dir.'/';
      }
    }

    // LOG::info('dir', $dir);

    return realpath($dir);
  }

  /**
   * Delete all files and subfolders in path (but not the path itself).
   *
   * @param mixed $level
   */
  public static function recursiveDeleteFromPath(string $path, $level = 0)
  {
    if (is_dir($path))
    {
      $files = array_diff(scandir($path), ['.', '..']);

      foreach ($files as $file)
      {
        self::recursiveDeleteFromPath(realpath($path).'/'.$file, $level + 1);
      }

      if ($level > 0)
      {
        return rmdir($path);
      }
      else
      {
        return true;
      }
    }
    elseif (is_file($path))
    {
      return unlink($path);
    }

    return false;
  }

  /**
   * Return file count and total size for all files (recursively)
   *  in cache directory for module/action (or module/_partial).
   *
   * @param mixed $route
   *
   * @return array
   */
  public static function getCacheSizeInfo($route)
  {
    $real_path = ManageSfCache::getRealPathForCache($route);
    // LOG::info('route', $real_path);

    if (false === $real_path)
    {
      return ['real_path' => '-NO CACHE FILE(s)-', 'file_count' => -1, 'size_kb' => -1];
    }

    $size = 0;

    if (is_dir($real_path))
    {
      $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real_path, FilesystemIterator::SKIP_DOTS));
      foreach ($iterator as $i)
      {
        $size += $i->getSize();
      }
      $file_count = iterator_count($iterator);
    }
    else
    {
      $size = filesize($real_path);
      $file_count = 1;
    }

    //echo sprintf('%d size, %d files', $size, iterator_count($iterator));

    return [
      'real_path' => $real_path,
      'file_count' => $file_count,
      'size_kb' => (int) ($size / 1024),
    ];
  }

  /**
   * Returns 'application/environment' (part of the cache folder path).
   */
  public static function getAppSlashEnv(): string
  {
    $env = strtolower(sfApplicationConfiguration::getActive()->getEnvironment());

    return self::APP_NAME.'/'.$env;
  }

  // returns array('module', 'action') for given $route
  public static function getModuleActionFromRoute($route)
  {
    [$module, $action] = explode('/', $route);

    return [$module, $action];
  }

  /**
   * Format host name as used by Symfony1.x in cache path name.
   *
   * In development it returns `localhost`
   *
   * In production it looks like `kanji_koohii_com`
   */
  public static function getHostName()
  {
    $context = sfContext::getInstance();
    $hostName = $context->getRequest()->getHost();

    $hostName = preg_replace('/[^a-z0-9\*]/i', '_', $hostName);
    $hostName = preg_replace('/_+/', '_', $hostName);

    return strtolower($hostName);
  }
}
