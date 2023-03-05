<?php

namespace Koohii\Scripts\Lib;

use CJK;
use Command_CLI;

define('UNIHAN_DATAFILES', SF_ROOT_DIR.'/data/datafiles/download/Unihan/');
define('UNIHAN_IRGSOURCES_TXT', UNIHAN_DATAFILES.'/Unihan_IRGSources.txt');

class UnihanIRGSourcesParser
{
  protected Command_CLI $cmd;

  /**
   * Map of $ucsId => stroke count.
   *
   * @var array<int, int>
   */
  private $kTotalStrokes;

  public function __construct($cmd)
  {
    $this->cmd = $cmd;

    $this->parse();
  }

  public function mergeFields(KanjisTable $kanjis)
  {
    $this->cmd->verbose(' ... merging strokecounts from Unihan');

    foreach ($kanjis->entries as $kEntry)
    {
      $ucsId = $kEntry->ucs_id;
      $kTotalStrokes = $this->kTotalStrokes[$ucsId] ?? 0;
      $kEntry->strokecount = $kEntry->strokecount ?: $kTotalStrokes;
    }
  }

  public function parse()
  {
    $handle = ParserUtils::fileOpen(UNIHAN_IRGSOURCES_TXT);

    $lineNr = 0;
    $unique = [];

    $this->kTotalStrokes = [];

    while (false !== ($cols = fgetcsv($handle, 1000, "\t")))
    {
      ++$lineNr;

      // skip enmpty lines
      if (null === $cols[0])
      {
        continue;
      }

      // skip comments
      if ($cols[0][0] === '#')
      {
        continue;
      }

      // parse the `U+xxxx` code point
      $ucsId = hexdec(substr($cols[0], 2));

      $unique[$ucsId] = true;

      // this should match CJK::isCJKUnifiedUCS()
      if ($ucsId < CJK::CJK_UNIFIED_BEGIN || $ucsId > CJK::CJK_UNIFIED_END)
      {
        continue;
      }

      if ($cols[1] === 'kTotalStrokes')
      {
        $strokeCount = (int) $cols[2];
        $this->kTotalStrokes[$ucsId] = $strokeCount;
      }
    }

    ParserUtils::fileClose($handle);

    $this->cmd->verbose(' ... parsed %d lines (%d unique codepoints)', $lineNr, count($unique));
  }
}
