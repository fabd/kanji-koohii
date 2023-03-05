<?php
/**
 * Parse the RTK index & keywords from "RTK Editions Compared" spreadsheet.
 *
 *   - kanji
 *   - frame number ie. "heisig index" for old/new editions
 *   - keyword : use the newer edition keyword when available
 *   - indexes for old & new editions
 *   - lesson numbers for old & new editions
 *
 * INSTRUCTIONS
 *
 *   The RTK Editions Compared must be exported to CSV for parsing:
 *   - remove the header rows 1 & 2
 *   - remove the empty A col, and unneeded extra cols I J K L
 *   - save as .csv with tab delimiters
 */

namespace Koohii\Scripts\Lib;

use CJK;
use Command_CLI;
use IntlChar;
use utf8;

define('RTK_EDITIONS_SPREADSHEET', SF_ROOT_DIR.'/data/datafiles/RTK Editions Compared.csv');

class RtkParser
{
  /**
   * Special value used for kanji that are in one edition, but not the other.
   *
   *   9998 kanji is NOT in old edition
   *   9999 kanji is NOT in new edition
   *
   * See Katsuo´s spreadsheet sheet 2 notes.
   */
  public const NOT_IN = 9998;

  private const CSV_SEPARATOR = "\t";

  private const CSV_KANJI = 0;
  private const CSV_IDXOLD = 1;
  private const CSV_IDXNEW = 2;
  private const CSV_LESOLD = 3;
  private const CSV_LESNEW = 4;
  private const CSV_KWOLD = 5;
  private const CSV_KWNEW = 6;

  protected Command_CLI $cmd;

  public function __construct(Command_CLI $cmd)
  {
    $this->cmd = $cmd;
  }

  /**
   * Parse the spreadsheet and merge RTK related data into the KanjisTable rows.
   */
  public function parse(KanjisTable $kanjisTable, KanjidicParser $kanjidic)
  {
    $handle = ParserUtils::fileOpen(RTK_EDITIONS_SPREADSHEET);

    $dicByKanji = $kanjidic->indexByKanji;

    // map to double-check for duplicate entries (can easily happen in a spreadsheet)
    // [ [ 'idx_old' => 1, 'idx_new' => 1 ], ... ]
    $kk = [];

    $lineNr = 0;

    while (false !== ($cols = fgetcsv($handle, 1000, self::CSV_SEPARATOR)))
    {
      ++$lineNr;

      // sanity check for empty or misformatted rows
      if (count($cols) !== 7)
      {
        $this->cmd->throwError(' Malformatted data at line %d', $lineNr);
      }

      $kanji = $cols[self::CSV_KANJI];

      // sanity check for duplicate rows
      if (isset($kk[$kanji]))
      {
        $this->cmd->echof(' Duplicate character at line %d', $lineNr);

        continue;
      }

      $idxOld = (int) $cols[self::CSV_IDXOLD];
      $idxNew = (int) $cols[self::CSV_IDXNEW];

      // set frame number to null for special values in Katsuo's spreadsheet
      $idxOld = $idxOld < self::NOT_IN ? $idxOld : null;
      $idxNew = $idxNew < self::NOT_IN ? $idxNew : null;

      $kk[$kanji] = [KanjisRow::IDX_OLD => $idxOld, KanjisRow::IDX_NEW => $idxNew];

      // sanity check that all RTK characters in the spreadsheet have valid UCS
      // $ucsId = (int) utf8::toCodePoint($cols[0]);
      $ucsId = IntlChar::ord($kanji);
      if (null === $ucsId || $ucsId < CJK::CJK_UNIFIED_BEGIN || $ucsId > CJK::CJK_UNIFIED_END)
      {
        $this->cmd->throwError(' ... invalid UCS code at line %d', $lineNr);
      }

      // sanity check that we have the matching entry in kanjidic
      $dicEntry = $dicByKanji[$kanji] || false;
      if (!$dicEntry)
      {
        $this->cmd->throwError(' kanjidic entry not set?? %s  at line %d', $kanji, $lineNr);
      }

      $row = $kanjisTable->indexByUCS[$ucsId];

      // if either index is not present, default to UCS (extended frame number)
      $row->{KanjisRow::IDX_OLD} = $idxOld ?? $row->ucs_id;
      $row->{KanjisRow::IDX_NEW} = $idxNew ?? $row->ucs_id;

      // assign a lesson only if index is valid (kanji *is* in OLD edition)
      $row->les_old = $idxOld !== null ? (int) $cols[self::CSV_LESOLD] : 0;

      // assign a lesson only if index is valid (kanji *is* in NEW edition)
      $row->les_new = $idxNew !== null ? (int) $cols[self::CSV_LESNEW] : 0;

      // use 6th edition keyword preferably, fall back to old edition
      $row->keyword = $cols[self::CSV_KWNEW] != '(not in)'
        ? $cols[self::CSV_KWNEW]
        : $cols[self::CSV_KWOLD];

      // echo sprintf(" (%05d) " . implode(' ... ', $cols) . "\n", $lineNr);
    }

    ParserUtils::fileClose($handle);

    $this->cmd->verbose(' ... parsed %d lines (%d unique kanji).', $lineNr, count($kk));

    // verify the old and new indexes
    $this->validateHeisigIndexCol($kk, KanjisRow::IDX_OLD, 3030);
    $this->validateHeisigIndexCol($kk, KanjisRow::IDX_NEW, 3000);
  }

  /**
   * Validate an index column in a two dimensional array (ignores null values).
   *
   * - checks that the index values are sequential
   * - checks that the index values start at 1 and end at $idxMax
   *
   * @param array  $kk
   * @param string $idxCol the column/property to check
   * @param int    $idxMax max frame number, inclusive
   */
  protected function validateHeisigIndexCol($kk, $idxCol, $idxMax)
  {
    $frameNums = array_column($kk, $idxCol);

    $frameNums = array_filter($frameNums, fn ($v) => $v !== null);

    sort($frameNums);

    $this->validateSequence($frameNums, 1);

    // check that the index is complete
    if (array_pop($frameNums) !== $idxMax)
    {
      $this->cmd->throwError(" ... sequence '%s' does not end at %d.\n", $idxCol, $idxMax);
    }

    $this->cmd->verbose(" ... sequence '%s' is valid (#1 to #%d).", $idxCol, $idxMax);
  }

  /**
   * Checks that the array is made of sequential integer values, starting at $start.
   *
   * @param int $start
   *
   * @return bool
   */
  protected function validateSequence(array $sequence, $start)
  {
    $check = $start;
    foreach ($sequence as $value)
    {
      if ($value !== $check++)
      {
        $this->cmd->throwError(' ... invalid sequence!');
      }
    }

    return true;
  }
}
