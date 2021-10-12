<?php

class CacheDictLookupPeer extends coreDatabaseTable
{
  protected $tableName = 'cache_dict_lookup';
  protected $columns = []; // timestamp columns must be declared for insert/update/replace

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
    if (false !== ($text = self::$db->fetchOne($select)))
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

    while (false !== ($row = self::$db->fetch()))
    {
      $results[] = json_decode($row['json']);
    }
    // DBG::printr($results);exit;

    return $results;
  }
}
