<?php
/**
 * Kanjis Peer.
 *
 * Methods:
 *  getKanjiByUCS()
 *  getKanjiByHeisig()
 *  getKanjiByCharacter()
 *  getKeyword()
 *  getDisplayKeyword()
 *  getFlashcardData()
 *  isHeisigIndexed()
 *
 * Helpers:
 *  joinLeft($select)       Adds LEFT JOIN USING(ucs_id) to the select.
 * 
 * 
 * @author  Fabrice Denis
 */

class KanjisPeer extends coreDatabaseTable
{
  protected
    $tableName = 'kanjis',
    $columns   = array();  // timestamp columns must be declared for insert/update/replace

  /**
   * This function must be copied in each peer class.
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Get character information, given the UCS-2 code value.
   *
   * Datatype conversions:
   *    ucs_id     =>   (int)
   *
   * @param   int    $ucsId   UCS-2 code value.
   *
   * @return  object  Kanjis table row data (plus 'framenum'), or false
   */
  public static function getKanjiByUCS($ucsId)
  {
    // FIXME   remove the validation code (validate higher up!! + cohesion)
    assert('ctype_digit($ucsId) && rtkIndex::isExtendedIndex($ucsId)');

    if (!BaseValidators::validateInteger($ucsId))
    {
      return false;
    }

    self::getInstance()->select()->where('ucs_id = ?', $ucsId)->query();

    if (false !== ($o = self::$db->fetchObject()))
    {
      // set "framenum" to the selected Heisig index, otherwise UCS
      $heisigNr = rtkIndex::getIndexForChar($o->kanji);
      $o->framenum = $heisigNr !== false ? $heisigNr : $ucsId;

      // normalize some values for cleaner code downhill
      $o->ucs_id = (int)$o->ucs_id;
      $o->strokecount = (int)$o->strokecount;
    }

    return $o;
  }

  /**
   * Return kanji data for given Heisig index number (aka "frame number").
   *
   * @param   string   $heisigNum   A Heisig index number (in the future, may be alphanumeric eg "123A")
   *
   * @return  object   Kanji rowdata as object, or FALSE
   */
  public static function getKanjiByHeisig($heisigNum)
  {
    assert('(int)$heisigNum < 0x3000');

    self::getInstance()->select()->where('framenum = ?', $heisigNum)->query();

    return self::$db->fetchObject();
  }

  /**
   * Return kanji data for given kanji as utf8 character.
   * 
   * @param   String   $utf8    Kanji character in utf8
   * @return  Object   Kanji rowdata as object, or FALSE
   */
  public static function getKanjiByCharacter($utf8)
  {
    if (empty($utf8))
    {
      return false;
    }
    self::getInstance()->select()->where('kanji = ?', $utf8)->query();
    return self::$db->fetchObject();
  }

  /**
   * Returns flashcard data for the flashcard reviews (both SRS and non-SRS).
   *
   * This is a uiFlashcardReview callback, the data ($id) must be sanitized!
   *
   * @param  int     $ucsId     UCS-2 code value.
   * @param  object  $options   Options for the flashcard format (optional)
   *
   * Options:
   *   yomi          (OPTIONAL) true to include example words with pronunciations
   *   api_mode      (OPTIONAL) true to return data according to API /review/fetch
   * 
   * @return mixed   Object with flashcard data, or null
   */
  public static function getFlashcardData($ucsId, $options = null)
  {
    if (false === ($cardData = self::getKanjiByUCS($ucsId)))
    {
      return null;
    }

    // make sure id is a Number in returned JSON
    $cardData->id = (int)$cardData->ucs_id;
    unset($cardData->ucs_id);

    sfProjectConfiguration::getActive()->loadHelpers(array('Tag', 'Url', 'Links'));

    // remove data not used by client
    if (CJ_HANZI)
    {
      sfProjectConfiguration::getActive()->loadHelpers('Pinyin');

      // hanzi reviews use Pinyin reading (onyomi)
      $pinyin = explode(',', $cardData->onyomi);
      $pinyin = array_slice($pinyin, 0, 2);
      $tones  = array();
      foreach ($pinyin as $tone)
      {
        array_push($tones, pinyin_ntod($tone));
      }

      $cardData->pinyin = content_tag('span', implode(', ', $tones), array('title' => implode(', ', $pinyin)));

      //$cardData->pinyin = implode(', ', $tones);
      unset($cardData->onyomi);

      // not needed by client, reduce JSON response
      unset($cardData->lessonnum);
    }
    else
    {
      // not needed by client, reduce JSON response
      unset($cardData->onyomi);
      unset($cardData->lessonnum);
      unset($cardData->idx_olded);
      unset($cardData->idx_newed);
    }

    if (isset($options->yomi))
    {
      // v_on, v_kun
      $highlight = isset($options->api_mode) ? array('[', ']') : array('<em>', '</em>');
      rtkLabs::getSampleWords($cardData->id, $cardData, $highlight);
    }

    // get custom keyword
    $userid = sfContext::getInstance()->getUser()->getUserId();
    $custom_keyword = CustkeywordsPeer::getCustomKeyword($userid, $ucsId);
    $keyword = null !== $custom_keyword ? $custom_keyword : $cardData->keyword;
  
    if (!isset($options->api_mode)) {
      $cardData->keyword = link_to_keyword($keyword, $cardData->kanji, array('title' => 'Go to the Study page', 'target' => '_blank'));
    }
    else {
      $cardData->keyword = $keyword;
    }

    // tweaks for api mode response
    if (isset($options->api_mode)) {
      // api doesn't return the kanji as a character
      unset($cardData->kanji);
    }

    // the data goes straight to the client via JSON
    return $cardData;
  }

  /**
   * Checks if a character has a known Heisig index (hardcoded sequence 0 and
   * sequence 1 = old/new or trad/simpl indexes).
   *
   * @return  bool
   */
  public static function isHeisigIndexed($ucsId)
  {
    $sequences = rtkIndex::getSequences();
    $indexCols = array('index1' => $sequences[0]['sqlCol'], 'index2' => $sequences[1]['sqlCol']);
    $select = self::getInstance()->select($indexCols)->where('ucs_id = ?', $ucsId)->query();
    if ($row = self::$db->fetch())
    {
      // anything above 0x3400 is an UCS code, below should be known Heisig indexes
      return ($row['index1'] > 0 && $row['index1'] < rtkIndex::RTK_UCS) 
          || ($row['index2'] > 0 && $row['index2'] < rtkIndex::RTK_UCS);
    }

    return false;
  }

  /**
   * Adds a left join clause using the UCS code point to get kanji data for flashcards.
   * 
   * @param coreDatabaseSelect $select 
   *
   * @return coreDatabaseSelect
   */
  public static function joinLeftUsingUCS($select)
  {
    return $select->joinLeftUsing(self::getInstance()->getName(), 'ucs_id');
  }  
}
