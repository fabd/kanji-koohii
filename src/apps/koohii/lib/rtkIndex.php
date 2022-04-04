<?php
/**
 * rtkIndex represents a unique index of characters and sequential frame
 * numbers, along with helper functions to convert between frame numbers
 * and characters.
 *
 * Unique indexes are represented in php code to save database querries.
 *
 *
 * "frame number"   refers to an integer index number in Heisig's books.
 *
 * "extended frame number"   means an Heisig index number, or an UCS-2 code (since UCS-2 code
 *  values are above the RTK/RTH indexes, there is no overlap).
 *
 *  getNumCharacters()
 *  getNumCharactersVol1()
 *  getNumCharactersVol3()
 *  getNumLessonsVol1()
 *
 *  getSequences()
 *  getDefaultUserSequence()
 *  getSequenceInfo()
 *  getSequenceName()
 *  getSequenceMap()
 *  getSqlCol()
 *
 *  isExtendedIndex($n)
 *  isValidHeisigIndex($num)
 * 
 *  getCharForIndex($id)
 *  getIndexForChar($cjk)
 *  getIndexForUCS($ucs)
 *  getUCSForIndex($n)
 *  convertToUCS(array $ids)
 * 
 *  getLessons()
 *  getLessonsDropdown()
 *  getLessonInfo($lessonId)
 *  getCharCountForLesson($lesson)
 *  getLessonForIndex($frameNr)
 *  getLessonTitleForIndex($frameNr)
 *  getProgressSummary($cur_framenum)
 * 
 *  createFlashcardSet($from, $to, $shuffle)
 * 
 */

class rtkIndex
{
  /** @var int Total kanji count in $kanjis (RTK1, RTK3, RTK1 Supplement) */
  protected $MAXKANJI_RTK = null;

  /** @var int Kanji count for RTK/RSH/RTH Volume 1 */
  protected $MAXKANJI_VOL1 = null;

  /** @var int Kanji count for complete RTK (Volume 1+3) RSH/RTH (Volume 1+2) */
  protected $MAXKANJI_VOL3 = null;

  /** @var int Max. lesson number in Volume 1. */
  protected $NUMLESSONS_VOL1 = null;

  /** @var array  Hash of lesson numbers => character count for all volumes of RTK/RSH/RTH. */
  protected $lessons;

  /**
   * All characters in RTK/RSH/RTH Volume 1-3 ordered by frame number,
   * so that the character offset in the string + 1 maps to Heisig indexes.
   * 
   * @var string
   */
  protected $kanjis;

  /**
   * Un frame number dont la valeur est >= que cette constante est considéré
   * comme un code UCS-2. Tous les frame numbers de RTK/RTH ne dépassent pas
   * 12288 (0x3000). La base de donnée utilise le code UCS-2.
   *
   * Unicode ranges:
   *
   *   U+4E00–U+9FBF   Kanji
   *   U+3040–U+309F   Hiragana
   *   U+30A0–U+30FF   Katakana
   *
   *   U+3400-U+2F907  Unihan datafiles
   */
  const RTK_UCS           = 0x3000;

  /**
   * Separator between multiple edition keywords (as stored in KanjisPeer table)
   * eg. 'village/town'
   *
   * Note: not used in RSH/RTH keywords yet.
   */
  const EDITION_SEPARATOR = '/';

  /**
   * Multiple indexes (FIXME move to rtk/rthSequences class ?).
   *
   *   sqlId  is used in the database for the user option, 
   *          MUST match the sequence array index
   */
  // sqlId of sequence set for new users by default
  static public $newuser_sequence = ['rth' => 1, 'rtk' => 1];

  // keep in sync with /modules/account/templates/sequenceView !
  static public $rtk_sequences = [
    0 => ['classId' => 'OldEdition', 'sqlId' => 0, 'sqlCol' => 'idx_olded'],
    1 => ['classId' => 'NewEdition', 'sqlId' => 1, 'sqlCol' => 'idx_newed']
  ];

  // keep in sync with /modules/account/templates/sequenceView !
  static public $rth_sequences = [
    0 => ['classId' => 'Traditional', 'sqlId' => 0, 'sqlCol' => 'idx_trad'],
    1 => ['classId' => 'Simplified',  'sqlId' => 1, 'sqlCol' => 'idx_simp']
  ];

  // require extending class based on the user's RTK sequence (5th vs 6th edition)
  public static function createInstance()
  {
    // load class that represents the unique index selected by the user
    $userSeq = rtkIndex::getSequenceInfo();
    $fileName = sfConfig::get('sf_app_lib_dir').'/model/'.CJ_MODE.'Index'.$userSeq['classId'].'.php';
    require($fileName);
    
    return new rtkIndexMeta();
  }

  public static function inst()
  {
    static $instance = null;
    $instance ??= self::createInstance();
    return $instance;
  }

  /**
   * Getters for sequence properties.
   *
   */
  public function getNumCharacters()
  {
    return $this->MAXKANJI_RTK;
  }

  public function getNumCharactersVol1()
  {
    return $this->MAXKANJI_VOL1;
  }

  public function getNumCharactersVol3()
  {
    return $this->MAXKANJI_VOL3;
  }

  public function getNumLessonsVol1()
  {
    return $this->NUMLESSONS_VOL1;
  }

  /**
   * Returns supported character sequences (multiple editions in RevTH,
   * Traditional and Simplified in RevTK).
   *
   * @return  array
   */
  public static function getSequences()
  {
    return CJ_HANZI ? self::$rth_sequences : self::$rtk_sequences;
  }

  /**
   * Returns sql id for the default sequence to user for new user accounts.
   *
   * @return  int     id of the sequence, corresponds to sqlId in sequences info
   */
  public static function getDefaultUserSequence()
  {
    return self::$newuser_sequence[CJ_MODE];
  }

  /**
   * Returns sequence info for the user's active sequence.
   * 
   * @return  array
   */
  public static function getSequenceInfo()
  {
    $seqId = sfContext::getInstance()->getUser()->getUserSequence();
    $sequences = self::getSequences();
    return $sequences[$seqId];
  }
  
  /**
   * Returns abbreviated name of the sequence displayed in the UI.
   *
   * @return  string  
   */
  public static function getSequenceName()
  {
    return self::inst()->shortName;
  }

  /**
   * Return a key-value pair array fo the user's RTK index (useful for Js Map()).
   *
   * @return array
   */
  public static function getSequenceMap()
  {
    mb_internal_encoding('utf-8');
    $kanjis = mb_str_split(self::inst()->kanjis);
    $map = [];
    $seqNr = 1;
    foreach ($kanjis as $char) {
      $ucsId = mb_ord($char);
      $map[] = [$ucsId, $seqNr++];
    }
    return $map;
  }

  /**
   * Returns the index column to use depending on the user's selected edition.
   * 
   * THIS FUNCTION MUST ENSURE A SAFE COLUMN NAME FOR QUERIES.
   *
   * @return  string  sql column name from the kanjis table
   */
  public static function getSqlCol()
  {
    $seqId = sfContext::getInstance()->getUser()->getUserSequence();
    $sequences = self::getSequences();
    assert($seqId >= 0 && $seqId < count($sequences));
    return $sequences[$seqId]['sqlCol'];
  }

  /**
   * Returns true if the index is likely to be a UCS code point (Heisig frame
   * numbers are well below the CJK Ideographs range).
   * 
   * @param   int     $n   An index number (Heisig, or extended UCS-2 code)
   *
   * @return  bool    
   */
  public static function isExtendedIndex($n)
  {
    return $n >= self::RTK_UCS;
  }

  /**
   * Returns true if the index number matches a Heisig frame number.
   *
   * @param  int  $num  A number
   * 
   * @return bool  True if number is valid RTK/RSH/RTH frame number
   */
  public static function isValidHeisigIndex($num)
  {
    return ($num >= 1 && $num <= self::inst()->getNumCharacters());
  }

  /**
   * Returns a tf8 character for a given Heisig frame number.
   *
   * Supports extended frame numbers: UCS-2 codes are converted to UTF8 characters.
   * 
   * @param  int      An extended RTK frame number (starts at 1)
   * 
   * @return mixed    Single utf8 character or null if the index number does
   *                  not match a heisig character.
   */
  public static function getCharForIndex($id)
  {
    $id = intval($id);

    assert($id > 0);

    if (self::isValidHeisigIndex($id))
    {
      return mb_substr(self::inst()->kanjis, $id - 1, 1, 'utf8');
    }
    else if (CJK::isCJKUnifiedUCS($id))
    {
      return utf8::fromUnicode([$id]);
    }

    return null;
  }

  /**
   * Returns a frame number (ie. Heisig index) for a given utf8 character.
   * 
   * @param  string  $char   A single utf8 character
   *
   * @return mixed   Frame number or false if the char is not in the index.
   */
  public static function getIndexForChar($char)
  {
    $pos = mb_strpos(self::inst()->kanjis, $char, 0, 'utf8');
    return (false !== $pos) ? $pos + 1 : false;
  }

  /**
   * Returns a Heisig index number for UCS codes that match a Heisig char.
   *
   * Alias: "get extended frame number".
   * 
   * @param  int    $ucs 
   *
   * @return int    Extended frame number (Heisig frame number, or UCS).
   */
  public static function getIndexForUCS($ucs)
  {
    $utf = utf8::fromUnicode($ucs);
    
    $index = rtkIndex::getIndexForChar($utf);

    return $index !== false ? $index : $ucs;
  }

  /**
   * Get the UCS code point of the character, given a extended Heisig index.
   *
   * @param  int   $n   An extended frame number (Heisig or UCS).
   *
   * @return mixed   UCS code point, or false if input is neither a Heisig index
   *                 or a valid CJK Unified code point.
   */
  public static function getUCSForIndex($n)
  {
    $n = intval($n);

    // index must be Heisig frame number or valid CJK Unified code point
    $c_utf = rtkIndex::getCharForIndex($n);

    return ($c_utf !== null) ? utf8::toCodePoint($c_utf) : false;
  }

  /**
   * Convert one or more extended frame numbers to UCS-2 code value.
   *
   * If the value is already in the CJK range, it is unchanged.
   * 
   * @param   integer|array   Extended frame numbers (index or UCS-2)
   *
   * @return  array   Array with sequence numbers (ie. Heisig) converted to UCS-2, others unchanged.
   */
  public static function convertToUCS(array $ids)
  {
    $ucsids = [];

    if (!is_array($ids))
    {
      $ids = [$ids];
    }

    for ($i = 0, $n = count($ids); $i < $n; $i++)
    {
      $id = $ids[$i];
      
      if (!self::isExtendedIndex($id))
      {
        if (null !== ($c = self::getCharForIndex($id)))
        {
          $ucsids[$i] = utf8::toCodePoint($c);
        }
        else
        {
          throw new sfException(__METHOD__." Invalid frame number ($id)");
        }
      }
      else
      {
        // assume this is a UCS-2 code, though it may still be an invalid
        // character (not a kanji).
        $ucsids[$i] = $id;
      }
    }

    return $ucsids;
  }

  /**
   * Returns lessons as a hash (id => kanji_count).
   * 
   * @return array
   */
  public static function getLessons()
  {
    return self::inst()->lessons;
  }

  /**
   * Returns hash for lessons dropdown selection (lesson_id => label).
   * 
   * @return array
   */
  public static function getLessonsDropdown()
  {
    $lessons = self::getLessons();
    $options = [];
    foreach ($lessons as $k => $v) {
      $options[$k] = "Lesson ${k} (${v} kanji)";
    }
    return $options;
  }

  /**
   * Returns lesson metadata given lesson id from getLessons().
   *
   * @return mixed   [from => (int), count => (int)]  or  null if invalid lesson
   */
  public static function getLessonInfo($lessonId)
  {
    $lessons = self::getLessons();

    $from = 1;

    foreach ($lessons as $lessNr => $lessCount) {
      if ($lessNr == $lessonId) {
        return ['from' => $from, 'count' => $lessCount];
      }
      $from += $lessCount;
    }
    return null;
  }

  /**
   * Returns count of kanji in given lesson.
   * 
   * @param  int $lesson   Lesson starts at 1 like in the book.
   * @return mixed   Count or false
   */
  public static function getCharCountForLesson($lesson)
  {
    $lessons = self::getLessons();
    return isset($lessons[$lesson]) ? $lessons[$lesson] : false;
  }
  
  /**
   * Returns lesson number given a Heisig frame number.
   * 
   * @param  int    $frameNr   A Heisig index number (frame number).
   *
   * @return int    Returns lesson number, 0 if the frame number is not valid.
   */
  public static function getLessonForIndex($frameNr)
  {
    $lesson = 0;
    $lessons = self::getLessons();
    if ($frameNr > 0 && $frameNr <= self::inst()->getNumCharacters())
    {
      $maxframe = 0;
      foreach ($lessons as $lesson => $count)
      {
        $maxframe += $count;
        if ($frameNr <= $maxframe)
        {
          break;
        }
      }
    }

    return $lesson;
  }

  /**
   * TODO   Refactor this code into the sequence-specific classes
   *
   * @return string
   */
  public static function getLessonTitleForIndex($frameNr)
  {
    $lessNr = self::getLessonForIndex($frameNr);
    $title  = '';

    if ($lessNr >= 1 && $lessNr <= self::inst()->getNumLessonsVol1())
    {
      $title = 'Lesson '.$lessNr;
    }
    else if ($lessNr === 56 && CJ_HANZI)
    {
      $title = 'Volume 2';
    }
    else if ($lessNr === 57)
    {
      $title = 'RTK Volume 3';
    }
    else if ($lessNr === 58)
    {
      $title = 'RTK1 Supplement';
    }
    else
    {
      $title = 'Character not in '.rtkIndex::inst()->shortName;
    }

    return $title;
  }

  public static function getStudyPos()
  {
    $user = sfContext::getInstance()->getUser();
    return ReviewsPeer::getHeisigProgressCount($user->getUserId());
  }

  /**
   * Returns progress data based on last learned Heisig frame number.
   *
   * Assumes there are no gaps in learned kanji since frame one.
   *
   * If RTK1 is completed => kanjitogo == 0, framenum == MAXKANJI_VOL1, curlesson == false
   *
   * If there is a gap in the RTK1 range, curlesson==false, framenum==false
   * 
   * Returns object:
   *  (bool|int) heisignum     Current Heisig frame number, or false if there are gaps
   *  (bool|int) curlesson     Current active lesson (based on next unlearned kanji)
   *  (int)      kanjitogo     Reamining unlearned kanji in current lesson
   * 
   * @return mixed  Progress info as an object, or null if RtK Vol 1. is completed.
   */
  public static function getProgressSummary()
  {
    $inst = self::inst();

    $o = (object) ['curlesson' => false, 'kanjitogo' => false];

    // determine active lesson if the user has added cards in order
    $o->heisignum = rtkIndex::getStudyPos();

    // if there are gaps, do not guesswork current lesson
    if ($o->heisignum !== false)
    {
      if ($o->heisignum >= $inst->MAXKANJI_VOL1)
      {
        $o->kanjitogo = 0;
      }
      else if ($o->heisignum < $inst->MAXKANJI_VOL1)
      {
        // find current lesson, 57 = RTK1 finished
        $o->curlesson = self::getLessonForIndex($o->heisignum + 1);

        // find out remaining unlearned kanji towards next lesson
        $maxframe = 0;
        foreach (self::getLessons() as $lesson => $kanjiCount)
        {
          $minframe = $maxframe + 1;
          $maxframe += $kanjiCount;
          if ($o->heisignum < $maxframe)
          {
            break;
          }
        }

        $o->kanjitogo = $kanjiCount - ($o->heisignum - $minframe + 1);
      }
    }

    return $o;
  }

  /**
   * Create an array of flashcard ids using sequence numbers.
   *
   * @param int  $from    Sequence number start
   * @param int  $to      Sequence number end
   * @param bool $shuffle True to randomize the cards
   *
   * @return int[]
   */
  public static function createFlashcardSet($from, $to, $shuffle = false)
  {
    // create array of UCS ids from sequential Heisig flashcard range
    $numCards = $to - $from + 1;
    $framenums = array_fill(0, $numCards, 1);
    for ($i = 0; $i < $numCards; ++$i)
    {
      $framenums[$i] = $from + $i;
    }

    if ($shuffle)
    {
      // shuffle
      shuffle($framenums);
    }

    $ids = rtkIndex::convertToUCS($framenums);

    return $ids;
  }

}
