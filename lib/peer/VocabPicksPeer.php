<?php
class VocabPicksPeer extends coreDatabaseTable
{
  protected
    $tableName  = 'vocabpicks',
    $columns    = array('created_on'); // timestamp columns must be declared for insert/update/replace

  /**
   * This function must be copied in each peer class.
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
    assert('$dictId > 0');
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
    $items  = self::$db->fetchCol($select);
    return $items ?: [];
  }
}
