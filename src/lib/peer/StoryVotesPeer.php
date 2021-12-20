<?php
/**
 * StoryVotes Peer.
 * 
 * 
 * @author  Fabrice Denis
 */

class StoryVotesPeer extends coreDatabaseTable
{
  protected $tableName = 'storyvotes';

  const ERROR_SELF_VOTE  = -1;   // "vote" value returned to client

  /**
   * This function must be copied in each peer class.
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }
 
  /**
   * Returns author id for one favourite (starred) story (which starred
   * story is returned is undefined).
   *
   * @return  bool|integer    False if no starred story, or author's userid.
   */
  public static function getStarredStory($userId, $ucsId)
  {
    $select = self::getInstance()->select('authorid')->where('userid = ? AND ucs_id = ? AND vote = 1', [$userId, $ucsId])->limit(1);

    return self::$db->fetchOne($select);
  }

  /**
   * TODO  Everywhere we use (authorId, ucs_id) it acts as a unique story
   *       identifier. The `sid` column in the new InnoDB schema for `stories` is not connected
   *       currently (just a placeholder primary key) so we don't use it.
   * 
   * @param   int     $userId    User id
   * @param   int     $authorId  Id of the author of the story
   * @param   int     $ucsId   UCS-2 code (character !!!)
   * @param   int     $isUpvote  true for star, false for report
   * 
   * @return object  Object for JSON response
   */
  public static function voteStory($userId, $authorId, $ucsId, $isUpvote)
  {
    // cannot vote for self (GreaseMonkey may bypass client-side testing for this?)
    if ($userId == intval($authorId))
    {
      return ['uid' => $authorId, 'sid' => $ucsId, 'vote' => self::ERROR_SELF_VOTE];
    }

    // already voted?
    $lastvote = self::getLastVote($authorId, $ucsId, $userId);

    // new vote or toggle vote
    if ($isUpvote)
    {
      $cur_vote = ($lastvote==1) ? 0 : 1;
      $UPD_STARS = ['+1','-1','+1'];
      $UPD_KICKS = ['+0','+0','-1'];
      $stars_inc = $UPD_STARS[$lastvote];
      $kicks_inc = $UPD_KICKS[$lastvote];
    }
    else
    {
      $cur_vote = ($lastvote==2) ? 0 : 2;
      $UPD_STARS = ['+0','-1','+0'];
      $UPD_KICKS = ['+1','+1','-1'];
      $stars_inc = $UPD_STARS[$lastvote];
      $kicks_inc = $UPD_KICKS[$lastvote];
    }

    self::getInstance()->replace(
      ['vote' => $cur_vote],
      ['authorid' => $authorId, 'ucs_id' => $ucsId, 'userid' => $userId]);

    // votes were de-normalized into stories for performance (causes a row lock with InnoDB)
    // NOTE: set updated_on to itself to avoid the timestamp update (cf. coreDatabaseTable)
    // NOTE: can use UNIQUE KEY user_stories

    $tableName = 'stories_shared';

    StoriesSharedPeer::getInstance()->update(
      [
        'stars'      => new coreDbExpr('stars'.$stars_inc),
        'reports'    => new coreDbExpr('reports'.$kicks_inc),
        'updated_on' => new coreDbExpr('updated_on')],
      'ucs_id = ? AND userid = ?', [$ucsId, $authorId]);

    $response = [
      'uid'      => $authorId,
      'sid'      => $ucsId,
      'vote'     => $cur_vote,
      'lastvote' => $lastvote,
      'stars'    => $stars_inc,
      'kicks'    => $kicks_inc
    ];

    // FIXME for now always invalidate the cache
    StoriesSharedPeer::invalidateStoriesCache($ucsId);

    // performance check (see one line in SharedStoriesComponent.js)
    if (null !== self::$db->getProfiler()) {
      $response['__debug_log'] = self::$db->getProfiler()->getDebugLog();
    }

    return $response;
  }

  /**
   * (authorId, ucs_id) acts as a unique story identifier
   *
   */
  protected static function getLastVote($authorId, $ucsId, $userId)
  {
    $select = self::getInstance()->select('vote')->where('authorid = ? AND ucs_id = ? AND userid = ?', [$authorId, $ucsId, $userId]);
    $result = self::$db->fetchOne($select);
    return intval($result);
  }
}
