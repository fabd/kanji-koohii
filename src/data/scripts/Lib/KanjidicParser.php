<?php

namespace Koohii\Scripts\Lib;

use SimpleXMLElement;

define('KANJIDIC2_XML_FILE', SF_ROOT_DIR.'/data/datafiles/download/kanjidic2.xml');

class KanjidicParser
{
  /**
   * An array of KanjidicEntry objects.
   *
   * @var KanjiDicEntry[]
   */
  public $entries;

  /**
   * Index entries by the literal (the CJK character).
   *
   * @var KanjiDicEntry[]
   */
  public $indexByKanji;

  /**
   * Index entries by UCS codepoint.
   *
   * @var KanjiDicEntry[]
   */
  public $indexByUCS;

  private string $fileName;

  /**
   * @param   string  KANJDIC2 xml file name
   */
  public function __construct()
  {
    $this->fileName = KANJIDIC2_XML_FILE;
    $this->entries = [];
    $this->indexByKanji = [];
    $this->indexByUCS = [];
  }

  /**
   * @return int number of entries succesfully parsed
   */
  public function parse()
  {
    $simpleXml = new SimpleXMLElement(file_get_contents($this->fileName));
    $children = $simpleXml->children();

    foreach ($children->character as $character)
    {
      $entry = new KanjidicEntry($character);

      $this->entries[] = $entry;
      $this->indexByKanji[$entry->literal] = $entry;
      $this->indexByUCS[$entry->codepoints['ucs']] = $entry;
    }

    return count($this->entries);
  }
}
