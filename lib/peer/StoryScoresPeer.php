<?php
/**
 * StoryScores Peer.
 * 
 * This table collects the total scores from StoryVotesPeer,
 * to speed up selects in the Study page, Shared Stories component.
 * 
 * 
 * @author  Fabrice Denis
 */

class StoryScoresPeer extends coreDatabaseTable
{
  const TABLE    = 'storiesscores';

  protected
    $tableName = 'storiesscores',
    $columns   = array();  // timestamp columns must be declared for insert/update/replace

  /**
   * This function must be copied in each peer class.
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }
}
