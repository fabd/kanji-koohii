<?php

namespace Koohii\Scripts\Lib;

use SimpleXMLElement;
use utf8;

class KanjidicEntry
{
  /**
   * Single utf8 character.
   *
   * @var string
   */
  public $literal;

  /**
   * @var int
   */
  public $strokecount = 0;

  /**
   *   ucs    => (as a decimal integer)
   *   jis208 => ...
   *
   * @var array
   */
  public $codepoints;

  /**
   * Japanese readings only.
   *
   *   array( 'ja_on'  => array(...),
   *          'ja_kun' => array(...))
   *
   * @var array|null
   */
  public $readings;

  public function __construct(SimpleXMLElement $character)
  {
    $this->literal = (string) $character->literal;

    $this->codepoints = $this->parseCodepoints($character->codepoint);

    // check that the ucs code point is correct
    if (utf8::toCodePoint($this->literal) !== $this->codepoints['ucs'])
    {
      printf(" ... codepoint mismatch for character %s (ucs %s)\n", $this->literal, $this->codepoints['ucs']);
    }

    $this->strokecount = (int) $character->misc->children()->stroke_count;

    // eg. 𠂉 has no reading section
    if (isset($character->reading_meaning))
    {
      $this->readings = $this->parseReadings($character->reading_meaning->children()->rmgroup->reading);
    }
  }

  private function parseCodepoints(SimpleXMLElement $codepoints): array
  {
    $cp = [];

    foreach ($codepoints->children() as $codepoint)
    {
      $attr = $codepoint->attributes();
      $cptype = (string) $attr['cp_type'];
      $cp[$cptype] = $cptype === 'ucs' ? hexdec($codepoint) : (string) $codepoint;
    }

    return $cp;
  }

  private function parseReadings($readings): array
  {
    // only parse japanese readings
    $read = ['ja_on' => [], 'ja_kun' => []];

    foreach ($readings as $reading)
    {
      $attr = $reading->attributes();
      $type = (string) $attr['r_type'];
      if ($type === 'ja_on' || $type === 'ja_kun')
      {
        array_push($read[$type], (string) $reading);
      }
    }

    return $read;
  }
}
