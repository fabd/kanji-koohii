<?php

class CacheDictLookupPeer extends coreDatabaseTable
{
  protected $tableName = 'cache_dict_lookup';

  /**
   * @return self
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * @param mixed $ucsId
   *
   * @return mixed Array of DictEntry, or false
   */
  public static function getDictListForUCS($ucsId)
  {
    $select = self::getInstance()->select('json')->where('ucs_id = ?', $ucsId);

    $json = false;

    // fixme? could test 'num_entries' instead of storing '[]'
    if (false !== ($text = self::getInstance()->getDb()->fetchOne($select)))
    {
      $json = json_decode($text, true);
      assert(!is_null($json));
    }

    return $json;
  }

  /**
   * Retrieve dict results for multiple kanjis.
   * 
   * FIXME : unfinished, should return a hashmap { ucs: Array<DictListEntry> }
   *
   * @param array $ucsIds array of UCS codes
   *
   * @return array of array of DictEntry
   */
  public static function getDictResultsFor(array $ucsIds)
  {
    $where = implode(',', $ucsIds);
    $select = self::getInstance()->select('json')->where('ucs_id IN (?)', $where);
    $select->query();

    $results = [];

    $db = self::getInstance()->getDb();

    while (false !== ($row = $db->fetch()))
    {
      $results[] = json_decode($row['json']);
    }
    // DBG::printr($results);exit;

    return $results;
  }
}
