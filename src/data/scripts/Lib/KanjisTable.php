<?php

namespace Koohii\Scripts\Lib;

use CJK;
use Illuminate\Support\Collection;
use IntlChar;
use rtKIndex;

class KanjisTable
{
  /**
   * Store just the data we need to output the `kanjis` table.
   *
   * @var KanjisRow[]
   */
  public $entries = [];

  /**
   * @var KanjisRow[]
   */
  public $indexByUCS = [];

  public function initialize()
  {
    $this->entries = [];

    for ($ucsId = CJK::CJK_UNIFIED_BEGIN; $ucsId <= CJK::CJK_UNIFIED_END; ++$ucsId)
    {
      $entry = new KanjisRow();

      $entry->ucs_id = $ucsId;

      // non-Heisig chars will default to UCS code point (aka "extended frame number")
      $entry->idx_old = $ucsId;
      $entry->idx_new = $ucsId;

      // default value for mysql import
      $entry->strokecount = 0;

      $this->entries[] = $entry;
      $this->indexByUCS[$ucsId] = $entry;
    }
  }

  /**
   * Reorder and maps entries by the given sequence (cf. rtkIndex $sequences).
   *
   * For now it's hardcoded old/new but could be easily extended to support
   *  arbitrary sequences such as JLPT 1-4.
   *
   * @param int $seqId
   */
  public function getEntriesBySeqId($seqId): array
  {
    $sequences = rtkIndex::getSequences();
    $indexKey = $seqId === 0 ? KanjisRow::IDX_OLD : KanjisRow::IDX_NEW;

    $array = Collection::make($this->entries)
      ->keyBy($indexKey)
      ->sortKeys()
      ->toArray()
    ;

    return $array;
  }

  /**
   * Output the main kanjis table for Kanji Koohii.
   *
   *   Schema:
   *     ucs_id          ... integer
   *     keyword         ... use new ed. if present, fallback to old ed.
   *     kanji           ...
   *     onyomi          ... the first ON reading in KANJIDIC (one of the main readings)
   *     idx_olded       ... Heisig index *or* UCS code ("extended frame num")
   *     idx_newed       ... Heisig index *or* UCS code ("extended frame num")
   *     strokecount     ... from KANJIDIC
   *
   *
   * Output only the necessary data for Kanji Koohii - in particular for
   * handling RTK Old & New edition indexes.
   *
   * The data is ordered by RTK New Edition index (this is mainly convenience
   * for debugging SQL, to have all RTK kanji are at the start of the table).
   *
   * @param string $fileName
   */
  public function output($fileName, KanjidicParser $kanjidic)
  {
    // FIXME - hardcoded New Edition
    $seqId = 1;

    // reindex kanji by the sequence index
    $indexByNewEd = $this->getEntriesBySeqId($seqId);

    // output just the fields we need for the app
    $handle = ParserUtils::fileOpen($fileName, 'wb');

    $lineNr = 0;

    foreach ($indexByNewEd as $idxNew => $kEntry)
    {
      $ucsId = $kEntry->ucs_id;

      // kanjidic entry (only ~12000 of the ~20000 CJK Unified Ideographs)
      $dicEntry = $kanjidic->indexByUCS[$ucsId] ?? null;

      $on_yomi = '';

      if ($dicEntry && $dicEntry->readings)
      {
        $on_yomi = $dicEntry->readings['ja_on'][0] ?? '';
      }

      // strokecount : use KANJIDIC if available, fall back to the UNIHAN value
      $strokecount = $dicEntry ? $dicEntry->strokecount : $kEntry->strokecount;

      $keyword = $kEntry->keyword ?? 'Unicode #'.$ucsId;

      $literal = $dicEntry ? $dicEntry->literal : IntlChar::chr($ucsId);

      // mysql table row
      $rowdata = [
        $ucsId,
        $keyword,
        $literal,
        $on_yomi,
        $kEntry->idx_old,
        $kEntry->idx_new,
        $strokecount,
      ];

      ParserUtils::outputTabularData($handle, $rowdata);

      ++$lineNr;
    }

    ParserUtils::fileClose($handle);

    echo sprintf(' ... done writing %d rows to "%s".', $lineNr, $fileName);
  }
}
