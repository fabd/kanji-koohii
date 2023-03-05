<?php

namespace Koohii\Scripts\Lib;

class KanjisRow
{
  // property names
  public const IDX_OLD = 'idx_old';
  public const IDX_NEW = 'idx_new';
  public const LES_OLD = 'les_old';
  public const LES_NEW = 'les_new';

  public int $ucs_id;

  // these will be UCS for non-Heisig chars (aka "extended frame numbers")
  public int $idx_old;
  public int $idx_new;

  public int $strokecount;

  // (set by RtKParser) lesson number (starting at 1), 0 if kanji is not in OLD edition
  public int $les_old;

  // (set by RtKParser) lesson number (starting at 1), 0 if kanji is not in NEW edition
  public int $les_new;

  // keyword from new edition if available, otherwise from old edition
  public string $keyword;
}
