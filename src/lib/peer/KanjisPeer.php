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
  protected $tableName = 'kanjis';

  /**
   * This function must be copied in each peer class.
   * @return self
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
    assert(ctype_digit($ucsId) && rtkIndex::isExtendedIndex($ucsId));

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
    assert((int)$heisigNum < 0x3000);

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
   * This is a uiFlashcardReview callback, $ucsId must be sanitized!
   *
   * Options:
   * 
   *   yomi          (API ONLY) true to include example words with pronunciations
   *   api_mode      (API ONLY) true to return data according to API /review/fetch
   *   
   * @param  int     $ucsId     UCS-2 code value.
   * @param  object|null  $options   Options for the flashcard format (optional)
   * @return object|null  Flashcard data, or null
   */
  public static function getFlashcardData($ucsId, $options = null)
  {
    if (false === ($cardData = self::getKanjiByUCS($ucsId))) {
      return null;
    }

    $userId = sfContext::getInstance()->getUser()->getUserId();

    // make sure id is a Number in returned JSON
    $cardData->id = (int)$cardData->ucs_id;
    unset($cardData->ucs_id);

    sfProjectConfiguration::getActive()->loadHelpers(['Tag', 'Url', 'Links']);

    // not needed by client, reduce JSON response
    unset($cardData->onyomi);
    unset($cardData->lessonnum);
    unset($cardData->idx_olded);
    unset($cardData->idx_newed);

    // API ONLY (Kanji Ryokucha) : return On/Kun example words
    if (isset($options->yomi)) {
      // v_on, v_kun
      rtkLabs::getSampleWords($cardData->id, $cardData, isset($options->api_mode));
    }

    // retrieve user's vocab picks, plus highlighted readings
    if (!isset($options->api_mode)) {
      // VocabPickArray
      $cardData->vocab = rtkLabs::getFormattedVocabPicks($userId, $cardData->id);
    }

    // coalesce keyword with user's custom keyword
    $custKeyword = CustkeywordsPeer::getCustomKeyword($userId, $ucsId);
    $cardData->keyword = $custKeyword ?? $cardData->keyword;

    // API ONLY (apps) : api doesn't return the kanji as a character
    if (isset($options->api_mode)) { 
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
    $indexCols = ['index1' => $sequences[0]['sqlCol'], 'index2' => $sequences[1]['sqlCol']];
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
