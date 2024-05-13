<?php
class VocabPicksPeer extends coreDatabaseTable
{
  protected $tableName = 'vocabpicks';

  // timestamp cols for self::insert/update/replace
  protected $columns = ['updated_on'];

  /**
   * This function must be copied in each peer class.
   * 
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Create or update a vocab link from jmdict (dictid) to kanji card (userid, ucsid).
   *  
   * @return boolean
   */
  public static function link($userId, $ucsId, $dictId)
  {
    assert($dictId > 0);
    if ($dictId <= 0) return;

    $data = ['dictid' => $dictId];

    return self::getInstance()->replace($data, ['userid' => $userId, 'ucs_id' => $ucsId]);
  }

  /**
   * Remove a vocab link.
   * 
   * @return boolean  Returns true if succesfull.
   */
  public static function unlink($userId, $ucsId)
  {
    return self::getInstance()->delete('userid = ? AND ucs_id = ?', [$userId, $ucsId]);
  }

  /**
   * Returns user's vocab picks for given character.
   *
   * @return  array
   */
  public static function getUserPicks($userId, $ucsId)
  {
    $select = self::getInstance()->select('dictid')->where('userid = ? AND ucs_id = ?', [$userId, $ucsId]);
    $db = self::getInstance()->getDb();
    $items  = $db->fetchCol($select);
    return $items ?: [];
  }

  /**
   * Adds the vocab picks and dictionary left joins to a query (chainable).
   *
   * Assumes the query already includes the kanjis table.
   *
   * @param coreDatabaseSelect $select
   * @param int                $userId  Match all user's dictionary words.
   *
   * @return coreDatabaseSelect
   */
  public static function addVocabPicksLeftJoinUsing($select)
  {
    $vocabpicks = self::getInstance()->getName();

    $select->joinLeftUsing($vocabpicks, ['userid', 'ucs_id']);
    $select->joinLeftUsing(rtkLabs::TABLE_JDICT, 'dictid');

    return $select;
  }
}
