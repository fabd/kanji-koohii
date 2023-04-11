<?php
/**
 * Script to generate the main `kanjis` table & output related indexes.
 *
 * UPDATES
 *   - March 2023 : fixed all commands, tested with current KANJIDIC & UNIHAN
 *
 * USAGE
 *
 *  Create the tab-delimited data for the `kanjis` table
 *  $ php data/scripts/kanjis_table.php -v -t -o data/datafiles/generated/table_kanjis.csv
 *
 *  Output keyword files for the Study page search dropdown
 *    (note the date/hash used for versioning needs to be updated in rtkIndex.php)
 *  $ php data/scripts/kanjis_table.php -v -o web/revtk/study/keywords-rtk-0.20230411.js -k 0
 *  $ php data/scripts/kanjis_table.php -v -o web/revtk/study/keywords-rtk-1.20230411.js -k 1
 *
 *  Output lesson maps that are included in `rtkIndex(New|Old)Edition.php`
 *  $ php data/scripts/kanjis_table.php -l 0 > old_lessons.txt
 *  $ php data/scripts/kanjis_table.php -l 1 > new_lessons.txt
 *
 *
 * STATS
 *   13108 kanjis parsed from KANJIDIC matching CJK Unified Ideographs range.
 *   20912 entries for the CJK Unified Ideographs range,
 *         including ALL the RSH/RTH Chinese characters.
 *
 *
 * MYSQL
 *
 *   Local import (docker `db` service):
 *   - copy table_kanjis.csv to ./docker/db/initdb.d/  (the mapped volume)
 *   - from the db container
 *     DELETE FROM kanjis;
 *     LOAD DATA LOCAL INFILE '/docker-entrypoint-initdb.d/table_kanjis.csv' INTO TABLE kanjis CHARACTER SET 'utf8' FIELDS TERMINATED BY '\t' ENCLOSED BY '';
 *
 *   Production:
 *     Export the kanjis table locally and SOURCE it on the server:
 *     $ mysqldump -uroot -proot db_github kanjis > /docker-entrypoint-initdb.d/kanjis.sql
 *     (move file to /src)
 *     (deploy --env prod --file kanjis.sql)
 *     (rtkprod ... dbprod ... SOURCE kanjis.sql)
 *
 *
 * NOTES
 *
 *  - Only includes "CJK Unified Ideographs" (0x4e00 - 0x9fff).
 *    https://en.wikipedia.org/wiki/CJK_Unified_Ideographs_(Unicode_block)
 *
 *
 * SOURCES
 *
 *   - KANJIDIC2 in XML format from Jim Breen's site
 *     http://www.edrdg.org/wiki/index.php/KANJIDIC_Project
 *
 *   - "RTK Editions Compared" spreadsheet by Katsuo (chrtokstd1)
 *
 *   - UNIHAN Database https://www.unicode.org/charts/unihan.html
 *
 *
 * LINKS
 *
 *   CJK Unified Ideographs
 *   http://en.wikipedia.org/wiki/CJK_Unified_Ideographs_%28Unicode_block%29
 */

namespace Koohii\Scripts;

require_once realpath(dirname(__FILE__).'/../..').'/lib/batch/Command_CLI.php';

require_once SF_ROOT_DIR.'/lib/CJK.php';

use CJK;
use Command_CLI;
use IntlChar;
use Koohii\Scripts\Lib\KanjidicParser;
use Koohii\Scripts\Lib\KanjisRow;
use Koohii\Scripts\Lib\KanjisTable;
use Koohii\Scripts\Lib\ParserUtils;
use Koohii\Scripts\Lib\RtkParser;
use Koohii\Scripts\Lib\UnihanIRGSourcesParser;
use rtKIndex;

define('CJK_COMMON_COUNT', CJK::CJK_UNIFIED_END - CJK::CJK_UNIFIED_BEGIN + 1);

class MyCommand extends Command_CLI
{
  public function __construct()
  {
    parent::__construct([
      'out|o=s' => 'Output filename (required) for either -t or -k',
      't' => 'Output the main table for import in the database',
      'l=i' => 'Output map of lesson numbers (cf. rtkIndexNewEdition.php)',
      'k=i' => 'Output javascript file for the Study search dropdown.',
    ]);

    mb_internal_encoding('utf-8');

    $this->main();
  }

  protected function outputFile(): string
  {
    $outfile = $this->getFlag('o');

    if (null === $outfile)
    {
      $this->throwError('Required output file (-o). Type --help for help.');
    }

    return $outfile;
  }

  protected function main()
  {
    $this->verbose("\nInitializing kanjis table (%d entries) ...", CJK_COMMON_COUNT);
    $kanjisTable = new KanjisTable();
    $kanjisTable->initialize();

    $this->verbose("\nParsing UnihanIRGSources ...");
    $unihan = new UnihanIRGSourcesParser($this);
    $unihan->mergeFields($kanjisTable);
    // dd($kanjisTable->indexByUCS[20007]); // not in KANJIDIC, should be 8 strokes

    $this->verbose("\nParsing KANJIDIC2_XML_FILE ...");
    $kanjidic = new KanjidicParser(KANJIDIC2_XML_FILE);
    $numEntries = $kanjidic->parse();
    $this->verbose(' ... succesfully parsed %d characters.', $numEntries);

    // on teste si tous les caractères RSH/RTH sont dans le range "CJK Unified Ideographs"
    // require_once('lib/RevthParser.php');
    // $r = new RevthParser($this);
    // $r->parseRthData(RTH_VS_RSH_DATA, $kanjidic);exit;

    $this->verbose("\nParsing RTK_EDITIONS_SPREADSHEET ...");
    $rtkParser = new RtkParser($this);
    $rtkParser->parse($kanjisTable, $kanjidic);

    if ($this->getFlag('t'))
    {
      $this->verbose("\nWriting kanji table data ...");
      $kanjisTable->output($this->outputFile(), $kanjidic);
    }
    elseif (null !== ($seqId = $this->getFlag('k')))
    {
      $sequences = rtkIndex::getSequences();
      assert(isset($sequences[$seqId]));

      $this->outputKeywords($this->outputFile(), (int) $seqId, $kanjisTable, $kanjidic);
    }
    elseif (null !== ($seqId = $this->getFlag('l')))
    {
      $sequences = rtkIndex::getSequences();
      assert(isset($sequences[$seqId]));

      $this->outputLessons((int) $seqId, $kanjisTable);
    }

    $this->verbose("\nFinished.");
  }

  /**
   * Output the lesson map that is included in `rtkIndexOld/NewEdition.php`.
   *
   * @param int $seqId
   */
  private function outputLessons($seqId, KanjisTable $kanjisTable)
  {
    $kanjisBySeq = $kanjisTable->getEntriesBySeqId($seqId);

    $lessCol = $seqId === 0 ? KanjisRow::LES_OLD : KanjisRow::LES_NEW;

    $lessons = [];

    foreach ($kanjisBySeq as $seqNr => $entry)
    {
      // ignore special values (9998, 9999)
      if ($seqNr >= RtkParser::NOT_IN)
      {
        break;
      }

      $lessNr = $entry->{$lessCol};
      assert('$lessNr > 0');

      if (!isset($lessons[$lessNr]))
      {
        $lessons[$lessNr] = 0;
      }

      ++$lessons[$lessNr];
    }

    $arr = [];
    foreach ($lessons as $lessNr => $count)
    {
      $arr[] = "{$lessNr} => {$count}";
    }

    echo sprintf("%s\n", implode(', ', $arr));
  }

  /**
   * Outputs keyword files, currently assumes index 0 and index 1 added to the
   * Kanjidic entries as idx_old and idx_new (0, 1), so it should
   * work for RTK (old, new) and RTH (rth, rsh).
   */
  private function outputKeywords(string $fileName, int $seqId, KanjisTable $kanjisTable, KanjidicParser $kanjidic)
  {
    $sequences = rtkIndex::getSequences();
    $seqClassId = $sequences[$seqId]['classId'];

    echo sprintf("\nWriting keywords file for sequence '%s' ...\n", $seqClassId);

    // FIXME some day this need to be generalized for 3+ indexes, use classId instead
    $indexKey = $seqId === 0 ? KanjisRow::IDX_OLD : KanjisRow::IDX_NEW;

    $kanjisBySeq = $kanjisTable->getEntriesBySeqId($seqId);

    // dump($kanjisBySeq[2200]);exit;
    // dd($kanjisTable->entries[10]);
    // dd(array_values($kanjisBySeq));

    $handle = ParserUtils::fileOpen($fileName, 'wb');

    $keywords = [];
    $kanjis = [];
    $seqNrCheck = 1;

    foreach ($kanjisBySeq as $indexNr => $entry)
    {
      // it is ordered by heisig nr, so extended frame nr (UCS) means we're done
      if (rtkIndex::isExtendedIndex($indexNr))
      {
        break;
      }

      // sanity check for sequential index
      if ($indexNr !== $seqNrCheck)
      {
        $this->throwError(__FUNCTION__.' Guru Meditation #239874');
      }

      $keywords[] = $entry->keyword;
      $kanjis[] = IntlChar::chr($entry->ucs_id);
      ++$seqNrCheck;
    }

    // output template
    $keywords_json = json_encode($keywords, JSON_UNESCAPED_UNICODE);
    $characters = implode('', $kanjis);
    $generatorTime = date('F j, Y G:i:s');
    $generatorFile = basename(__FILE__);

    $s = <<<EOD
/**
 * This file was generated by script "{$generatorFile}"
 * 
 * Keywords file for sequence id: "{$seqClassId}"
 * 
 * @date    {$generatorTime}
 */
window.KK || (KK = {});
KK.SEQ_KEYWORDS = {$keywords_json};

KK.SEQ_KANJIS='{$characters}';
EOD;
    fwrite($handle, $s);

    ParserUtils::fileClose($handle);

    echo sprintf(" ... done writing file (%d characters).\n", mb_strlen($characters));
  }
}

$cmd = new MyCommand();
