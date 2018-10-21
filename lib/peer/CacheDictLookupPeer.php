<?php
class CacheDictLookupPeer extends coreDatabaseTable
{
  protected
    $tableName  = 'cache_dict_lookup',
    $columns    = array(); // timestamp columns must be declared for insert/update/replace

  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * 
   * 
   * @return mixed    Array of DictEntry, or false
   */
  public static function getDictListForUCS($ucsId)
  {
    $select = self::getInstance()->select('json')->where('ucs_id = ?', $ucsId);

    $json = false;

    // fixme? could test 'num_entries' instead of storing '[]'
    if (false !== ($text = self::$db->fetchOne($select))) {
      $json  = json_decode($text, true);
      assert('!is_null($json)');
    }

    return $json;
  }
}
