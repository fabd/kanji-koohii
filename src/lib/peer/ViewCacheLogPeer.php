<?php
/**
 * A very simple table to log cached content hit & misses.
 *
 *   flag()
 *   log()
 *
 * @author     Fabrice Denis
 */

/*

CREATE TABLE `log_viewcache`
(
  `cache_hit`    TINYINT   NOT NULL,
  `resource_id`  SMALLINT  UNSIGNED NOT NULL,
  `created_on`   TIMESTAMP NOT NULL DEFAULT 0,
  KEY `created_on` (`created_on`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

*/

class ViewCacheLogPeer extends coreDatabaseTable
{
  protected $tableName = 'log_viewcache';

  // timestamp cols for self::insert/update/replace
  protected $columns = ['created_on'];

  private static $viewIds = [];

  /**
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Return false here to disable reporting in the backend.
   *
   */
  public static function isEnabled()
  {
    return true;
  }

  /**
   * Call from *within* the view template to signal that this template is being
   * rebuilt (and hence, the cache is a miss).
   *
   * @param   string  $viewId   Unique id for the template, see log()
   */
  public static function flag($viewId)
  {
    self::$viewIds[$viewId] = true;
  }

  /**
   * Log one access of a view. $viewId is used to identify the view that should
   * be marked *within* the template with ::flag(). Thus if called from within the
   * the template, we know this view's cache is being rebuilt, otherwise it is
   * a hit.
   *
   * @param   string  $viewId       A short made-up name to uniquely identify a template
   * @param   int     $resourceId   A custom value to be logged
   *
   */
  public static function log($viewId, $resourceId)
  {
    // if not flagged, then the cached template is being used
    $cacheHit = (int) !isset(self::$viewIds[$viewId]);

    $data = [
      'cache_hit'    =>  $cacheHit,
      'resource_id'  =>  $resourceId
    ];

    return self::getInstance()->insert($data);
  }

  /**
   * Used by the backend to display cache efficiency.
   *
   */
  public static function getStats()
  {
    if (!self::isEnabled()) {
      return false;
    }

    self::getInstance()->select([
      'hit_count'   => 'SUM(cache_hit)',
      'total'       => 'COUNT(*)'
    ])->query();

    $result = self::$db->fetchObject();

    if ($result === false || $result->total == 0) {
      return false;
    }

    return [
      'hit_count'     => $result->hit_count,
      'miss_count'    => $result->total - $result->hit_count,
      'hit_percent'   => round( $result->hit_count / $result->total * 100 )
    ];
  }
}
