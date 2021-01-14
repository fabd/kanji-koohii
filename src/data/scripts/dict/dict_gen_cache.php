<?php
/**
 * Pre-populate dictionary search results for the DictLookupDialog
 * used in the kanji flashcard review modes.
 * 
 * Currently the main value is to be able to highlight the current
 * kanji in the dictionary results, without needing to do additional
 * queries to dictprons/dictsplit.
 * 
 *
 * USAGE
 *
 *   - Create the table (cf. data/schemas/incremental/rtk_0017_...)
 *
 *   - Populate table
 *     $ php data/scripts/dict/dict_gen_cache.php -g --limit 5000
 *
 *
 * DATA STRUCTURES
 *
 *  DictEntry  (placeholder name for searching through the code)
 *
 *    id     dictid (JMDICT entseq) (more or less, see data/scripts/dict/parse_jdict.pl 's note on <ent_seq>)
 *    c      compound, eg. "好む"
 *    r      reading with kanji highlighted, eg.  "(この)む"
 *    g      glossary
 *    pri    priority, bitmask (cf rtkLabs::$pricodes)
 *
 */

require_once(realpath(dirname(__FILE__).'/../../..').'/lib/batch/Command_CLI.php');

require_once(SF_ROOT_DIR.'/lib/CJK.php');


// configuration

  // options for the JSON encoding of the cached results
  define('JSON_ENCODE_OPTIONS', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  // define('JSON_ENCODE_OPTIONS', JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

  // filter for commone entries: ichi1, news1, news2 (see rtkLabs.php)
  define('JDICT_RESULTS_PRI', 0xCA);

  // check that the stored objects are not truncated!
  // 
  //   Find the offenders:
  //   SELECT ucs_id, CHAR_LENGTH(json) FROM cache_dict_lookup WHERE CHAR_LENGTH(json) > 5000;
  define('JSON_SIZE_LIMIT', 6150);

  // limit the number of DictEntry's because very common kanji can have 200+ and json is large!
  //  (it's fine, we sort the entries by JMDICT priority)
  define('DICTENTRYARRAY_LIMIT', 50);


class MyCommand extends Command_CLI
{
  private
    $db        = null,
    $tableName = '';


  public function __construct()
  {
    parent::__construct([
      'g'        => 'Generate cache',
      'limit=i'  => 'Limit (debugging)'
    ]);

    if ($this->getFlag('g')) {
      $this->main();
    }

  }

  protected function main()
  {
    mb_internal_encoding('utf-8');

    $db = sfProjectConfiguration::getActive()->getDatabase();

    $this->tableName = CacheDictLookupPeer::getInstance()->getName();

    $kanjis = $this->getUniqueRtkKanji($db);

    // reset table
    if (false === $db->query("DELETE FROM {$this->tableName}")) {
      $this->throwError('query failed');
    }

    $this->generateDictResults($db, $kanjis);

    $this->echof("\nFinished.");
  }

  private function generateDictResults($db, $kanjis)
  {
    $limit = (int) $this->getFlag('limit', 1);

    $count = 0;

    foreach ($kanjis as $ucsId)
    {
      $DictResults = $this->getDictResultsForUCS($db, $ucsId);
      
      $this->addToCache($db, $ucsId, $DictResults, $count);

      $count++;

      sleep(0.01);

      if (--$limit <= 0) break;
    }

    $verifyCount = (int) $db->fetchOne("SELECT COUNT(*) FROM {$this->tableName}");

    $this->echof("  GENERATED %d CACHED RESULTS  (%d succesfully stored).", $count, $verifyCount);

  }

  private function addToCache($db, $ucsId, $DictResults, $progressCount)
  {
    $json       = json_encode($DictResults, JSON_ENCODE_OPTIONS);
    $numEntries = count($DictResults);

    $this->echof("\n  #%d  DICT RESULTS FOR  UCS ( %d ) ...\n", $progressCount, $ucsId);
    if ($this->isVerbose) { var_dump($json); }

    $json_char_length = mb_strlen($json);
    if ($json_char_length > JSON_SIZE_LIMIT) {
      $this->throwError("\n  WARNING!  json encoded DictEntry is too large for column (%d chars)", $json_char_length);
    }

    $data = [
      'ucs_id'      => $ucsId,
      'num_entries' => $numEntries,
      'json'        => $json
    ];

    if (false === CacheDictLookupPeer::getInstance()->insert($data)) {
      $this->throwError("insert() failed for ucs_id ${ucsId}");
    }
  }

  private function getDictResultsForUCS($db, $ucsId)
  {
     
    $o = [];

    // this is a local one-off script, keep it simple for now
    $q = "SELECT jd.dictid, jd.compound, jd.reading, jd.glossary, jd.pri"
       . " FROM jdict jd"
       . " JOIN dictsplit ds USING(dictid)"
       . " WHERE kanji = ${ucsId}"
       . " AND (jd.pri & ".JDICT_RESULTS_PRI.")"
       . " ORDER BY jd.pri DESC"
       . " LIMIT ".DICTENTRYARRAY_LIMIT;

    $DictResultsArray = [];

    $dictitems = $db->fetchAll($q);

    foreach ($dictitems as $d) 
    {
      // create surrounding tags for the highlighted reading of the kanji
      $reading  = rtkLabs::getFormattedReading($db, $d['dictid'], $ucsId, ['(', ')'], $d['reading']);

      // use shorthands to optimize storage
      $DictResultsArray[] = [
        'id'  => $d['dictid'],
        'c'   => $d['compound'],
        'r'   => $reading,
        'g'   => $d['glossary'],
        'pri' => $d['pri']
      ];
// echo var_dump($DictResultsArray);exit;
    }

    return $DictResultsArray;
  }

  // get unique kanji across currenty supported indexes
  private function getUniqueRtkKanji($db)
  {
    // only old/new editions for now, so keep it simple

    $select = sprintf("select ucs_id FROM kanjis WHERE idx_olded < %s", rtkIndex::RTK_UCS);
    $kanjisOldEd = $db->fetchCol($select);

    $select = sprintf("select ucs_id FROM kanjis WHERE idx_newed < %s", rtkIndex::RTK_UCS);
    $kanjisNewEd = $db->fetchCol($select);

    $kanjis = array_unique(array_merge($kanjisOldEd, $kanjisNewEd));

    $this->verbose('  %d unique kanji in Old/New indexes.', count($kanjis));

    return $kanjis;
  }
}

$cmd = new MyCommand();

