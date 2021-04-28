<?php
/**
 * StoriesShared Peer.
 * 
 * This table acts as a sub index to the publicly shared stories, which represents
 * only 6-7% of 6+ million rows.
 *
 * Our first solution was to de-normalize stars and reports into the stories
 * table. This works fine but uses approx 150mb of indexes. Since the shared
 * stories list is only public stories... this wastes a lot of index space.
 *
 * Our current solution is to maintain stars, reports, and references to the
 * main Stories table with the primary 'sid' key which is an AUTO INCREMENT on
 * the stories table. We use 'stories_shared' to select public stories, and
 * sort them. The indexes are much smaller since we index only public stories.
 *
 * We can also avoid "Using filesort" by proper design of the indexes.
 *
 * See:
 *   data/schemas/incremental/rtk_1000_performance_update.sql
 * 
 * @author  Fabrice Denis
 */

class StoriesSharedPeer extends coreDatabaseTable
{
  protected
    $tableName = 'stories_shared',
    $columns   = [];  // timestamp columns must be declared for insert/update/replace

  /**
   * This function must be copied in each peer class.
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Invalidate the Shared Stories list in the Study pages after updates to
   * votes / reports.
   * 
   * @param   int     $ucsId   UCS-2 code for the kanji (the cache key)
   */
  public static function invalidateStoriesCache($ucsId)
  {
    $viewCacheManager = sfContext::getInstance()->getViewCacheManager();
    if (null !== $viewCacheManager) {
      $viewCacheManager->remove('@sf_cache_partial?module=study&action=_SharedStories&sf_cache_key='.$ucsId);
    }
  }

  /**
   * Update or insert a new row refering to a publicly shared story.
   *
   * Since the schema update the COUNT(*) of rows in `stories_shared` should
   * correspond to the count of rows in the `p_pub` partition of `stories`, since
   * that partition contains only the public stories.
   *
   * We still however use this separate table so we don't have a votes/reports
   * columns as well as the index on millions of private stories.
   *
   * @param   int     $storyId   stories PK
   * @param   int     $ucsId     UCS-2 code
   * @param   int     $userId    users PK
   * @param   bool    $isPublic  true if the story is shared
   *
   */
  public static function updateStoryRef($storyId, $ucsId, $userId, $isPublic)
  {
    $inst = self::getInstance();

    $exists = $inst->count('sid = ?', $storyId) > 0;

    if ($exists && $isPublic)
    {
      // nothing to change
      return true;
    }
    else if ($exists && !$isPublic)
    {
      // no longer shared
      return $inst->delete('sid = ?', $storyId);
    }
    else if (!$exists && $isPublic)
    {
      // FIXME for now, maintain votes if made public again
      $count_votes   = (int) StoryVotesPeer::getInstance()->count('authorid = ? AND ucs_id = ? AND vote = 1', [$userId, $ucsId]);
      $count_reports = (int) StoryVotesPeer::getInstance()->count('authorid = ? AND ucs_id = ? AND vote = 2', [$userId, $ucsId]);

      // new public story (or one that was private for a time...)
      $data = [
        'sid'         => $storyId,
        'ucs_id'      => $ucsId,
        'userid'      => $userId,
        'updated_on'  => new coreDbExpr('NOW()'),
        'stars'       => $count_votes,
        'reports'     => $count_reports
      ];

      return $inst->insert($data);
    }
  }

  public static function deleteStoryRef($storyId)
  {
    assert(is_int($storyId));
    return self::getInstance()->delete('sid = ?', $storyId);
  }
}
