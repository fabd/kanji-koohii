<?php
/**
 * rtkImportKeywords
 * 
 * Class that represents a customized keyword import selection, plus validation helpers.
 *
 * @author     Fabrice Denis
 */

class rtkImportKeywords
{
  protected
    $request    = null,
    $parsed     = [],
    $keywords   = [];

  const
    MAX_KEYWORD = 40;
  
  /**
   * 
   * @param  object  $request  Object with setError() method
   * @return 
   */
  public function __construct($request)
  {
    $this->request = $request;
  }
  
  /**
   * Parse data into kanji => keyword associations.
   *
   * @param  string $selection 
   *
   * @return boolean  Returns false if any error happended during parse or selection is empty.
   */
  public function parse($selection)
  {
    $parse = [];

    // split on newlines
    $rows =  preg_split('/\s*[\r\n]+\s*/u', $selection);

    // first parse into a sortable array
    for ($i = 0, $n = count($rows); $i < $n; $i++)
    {
      if (1 !== preg_match('/^\s*([^,\s\x{3000}]+)[,\s\x{3000}]+(.*)$/u', $rows[$i], $parts))
      {
        continue;
      }

      $id = $parts[1];
      // keyword, unquote and unescape
      $keyword = trim(ExportCSV::unquoteString($parts[2], true));

      $parse[] = [$id, $keyword, $i + 1];
    }

    if (count($parse) <= 0)
    {
      $this->request->setError('x', 'Import data could not be parsed.');
      return;
    }

    //$this->parsed = $parse;

    return $this->request->hasErrors() ? false : $parse;
  }

  /**
   * Validate the imported keyword data, and prepare session data.
   * 
   * @return bool  Returns true if the imported data is valid.
   */
  public function validate($data)
  {
    $parse = [];

    for ($i = 0, $n = count($data); $i < $n; $i++)
    {
      list($id, $keyword, $lineNr) = $data[$i];

      if (!self::validateKeyword($keyword, $this->request, $lineNr))
      {
        return false;
      }

      // id is heisig index, UCS-2 code or utf-8 character
      $ucsId = ctype_digit($id) ? rtkIndex::getUCSForIndex(intval($id)) : utf8::toCodePoint($id);

      if (!self::validateKanji($ucsId, $this->request, $lineNr))
      {
        return false;
      }

      // the key for sorting heisig numbers before UCS codes
      $c_ext = rtkIndex::getIndexForUCS($ucsId);

      // use extended framenum key for sorting Heisig indexes before UCS codes
      $parse[$c_ext] = [$ucsId, $keyword];
    }

    // sort on extended frame numbers so that Heisig indexes come before non-Heisig UCS codes
    ksort($parse);

    // prepare serializable array as ucs => keyword
    $keywords = [];
    foreach ($parse as $index => $data)
    {
      $keywords[$data[0]] = $data[1];
    }
    $this->keywords = $keywords;

//DBG::printr($keywords);DBG::printr($this->request->getErrors());exit;

    return true;
  }

  /**
   * Checks if the character is allowed to have a customized keyword (limit by
   * design for the time being).
   *
   * @param   int          $ucsId     UCS-2 code
   * @param   coreRequest  $request   Sets an error message, if any.
   *
   * @return  bool    true if the character (UCS) is valid and allowed for customized keywords.
   */
  public static function validateKanji($ucsId, $request, $lineNr = null)
  {
    if (!CJK::isCJKUnifiedUCS($ucsId))
    {
      $request->setError('x', sprintf('Unsupported character (Heisig index, UCS code or kanji) %s', self::atLine($lineNr)));
      return false;
    }

    return true;
  }

  public static function validateKeyword($keyword, $request, $lineNr = null)
  {

    if (empty($keyword) || preg_match('/^\s*$/', $keyword))
    {
      $request->setError('x', sprintf('Empty keyword %s.', self::atLine($lineNr)));
      return false;
    }

    if (strip_tags($keyword) !== $keyword)
    {
      $request->setError('x', 'HTML is not allowed in customized keyword '.self::atLine($lineNr).' ("'.$keyword.'")');
      return false;
    }

    if (mb_strlen($keyword) > self::MAX_KEYWORD)
    {
      $request->setError('x', 'Keyword is too long (max. '.self::MAX_KEYWORD.' characters) '.self::atLine($lineNr));
      return false;
    }

    return true;
  }

  /**
   * Return line number string for error messages, or nothing if it is null.
   *
   * @return string
   */
  public static function atLine($lineNr)
  {
    if ($lineNr !== null)
    {
      return ' at line '.$lineNr;
    }

    return '';
  }

  public function getTableHead()
  {
    $thead = [
      '<th width="5%"><span class="hd">Index#</span></th>',
      '<th width="10%"><span class="hd">Kanji</span></th>',
      '<th width="85%"><span class="hd">Imported&nbsp;Keyword</span></th>'
    ];

    return implode('', $thead);
  }

  public function getTableBody()
  {
    sfProjectConfiguration::getActive()->loadHelpers(['SimpleDate', 'CJK']);

    $rows = [];
    foreach ($this->keywords as $ucs => $keyword)
    {
      // display Heisig index if possible
      $c_utf = rtkIndex::getCharForIndex($ucs);
      $c_ext = rtkIndex::getIndexForChar($c_utf);
      $c_ext = $c_ext !== false ? $c_ext : $ucs;

      // framenumber (extended), kanji, keyword
      $rows[] = '<tr><td class="text-center>'.$c_ext.'</td>'
              . '<td class="kanji">'.cjk_lang_ja('&#'.$ucs.';').'</td>'
              . '<td>'.esc_specialchars($keyword).'</td></tr>';
    }

    return implode("\n", $rows);
  }

  public function getKeywords()
  {
    return $this->keywords;
  }
  
  public function getCount()
  {
    return count($this->keywords);
  }

  /**
   * Serialize methods to save user selection between requests.
   * 
   * @return 
   */
  public function __sleep()
  {
    return ['keywords'];
  }

  public function __wakeup()
  {
  }
}
