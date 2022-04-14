<?php
/**
 * rtkIndex represents a unique index of characters and sequential frame
 * numbers, along with helper functions to convert between frame numbers
 * and characters.
 *
 * TODO
 *   Someday all "Heisig" reference will be replaced with "Sequence"
 *   so we can have JLPT or RTK 3 sequences. A sequence number will
 *   remain like Heisig indexes, an integer starting at 1 -- but well
 *   below the UCS range.
 *
 *
 * Unique indexes are represented in php code to save database queries.
 *
 * Notes!
 *
 *   "frame number" (or "sequence number")
 *     an index number in Heisig's books (integer)
 *
 *   "extended frame number"
 *     an Heisig number, OR a UCS-2 code (since UCS code values are above
 *     the RTK/RTH indexes, there is no overlap).
 *
 * Methods:
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
 *  isExtendedIndex($extNr)
 *  isValidHeisigIndex($seqNr)
 *
 *  getIndexForChar($cjk)
 *  getIndexForUCS($ucs)
 *
 *  getCharForIndex($extNr)
 *  getUCSForIndex($extNr)
 *
 *  convertToUCS(array $ids)
 *
 *  getLessons()
 *  getLessonsDropdown()
 *  getLessonForIndex($seqNr)
 *  getLessonData($lessonId)
 *
 *  createFlashcardSet($from, $to, $shuffle)
 */
class rtkIndex
{
  /** @var int Total kanji count in (RTK1, RTK3, RTK1 Supplement) */
  protected $MAXKANJI_RTK;

  /** @var int Kanji count for RTK/RSH/RTH Volume 1 */
  protected $MAXKANJI_VOL1;

  /** @var int Kanji count for complete RTK (Volume 1+3) RSH/RTH (Volume 1+2) */
  protected $MAXKANJI_VOL3;

  /** @var int Max. lesson number in Volume 1. */
  protected $NUMLESSONS_VOL1;

  /** @var int[] Hash of lesson numbers => character count for all volumes of RTK/RSH/RTH. */
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
  public const RTK_UCS = 0x3000;

  /**
   * Separator between multiple edition keywords (as stored in KanjisPeer table)
   * eg. 'village/town'.
   *
   * Note: not used in RSH/RTH keywords yet.
   */
  public const EDITION_SEPARATOR = '/';

  /**
   * Multiple indexes (FIXME move to rtk/rthSequences class ?).
   *
   *   sqlId  is used in the database for the user option,
   *          MUST match the sequence array index
   */
  // sqlId of sequence set for new users by default
  public const NEWUSER_SEQUENCE = 1;

  // keep in sync with /modules/account/templates/sequenceView !
  public static $rtk_sequences = [
    0 => ['classId' => 'OldEdition', 'sqlId' => 0, 'sqlCol' => 'idx_olded'],
    1 => ['classId' => 'NewEdition', 'sqlId' => 1, 'sqlCol' => 'idx_newed'],
  ];

  // require extending class based on the user's RTK sequence (5th vs 6th edition)
  public static function createInstance()
  {
    // load class that represents the unique index selected by the user
    $userSeq = rtkIndex::getSequenceInfo();
    $fileName = sfConfig::get('sf_app_lib_dir').'/model/'.CJ_MODE.'Index'.$userSeq['classId'].'.php';

    require $fileName;

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
   * Returns supported character sequences (multiple editions in RTK).
   *
   * @return array
   */
  public static function getSequences()
  {
    return self::$rtk_sequences;
  }

  /**
   * Returns sql id for the default sequence to user for new user accounts.
   *
   * @return int id of the sequence, corresponds to sqlId in sequences info
   */
  public static function getDefaultUserSequence()
  {
    return self::NEWUSER_SEQUENCE;
  }

  /**
   * Returns sequence info for the user's active sequence.
   *
   * @return array
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
   * @return string
   */
  public static function getSequenceName()
  {
    return self::inst()->shortName;
  }

  /**
   * Return an array of [[ucsId, seqNr], ...] for the current sequence.
   *
   * Useful to instance a Map() in Javascript, for matching UCS > Heisig index.
   *
   * @return array
   */
  // public static function getSequenceMap()
  // {
  //   mb_internal_encoding('utf-8');
  //   $kanjis = mb_str_split(self::inst()->kanjis);
    
  //   $map = [];
  //   $seqNr = 1;
  //   foreach ($kanjis as $char)
  //   {
  //     $map[] = [mb_ord($char), $seqNr++];
  //   }

  //   return $map;
  // }

  /**
   * Returns the index column to use depending on the user's selected edition.
   *
   * THIS FUNCTION MUST ENSURE A SAFE COLUMN NAME FOR QUERIES.
   *
   * @return string sql column name from the kanjis table
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
   * @param int $extNr An index number (Heisig, or extended UCS code)
   *
   * @return bool
   */
  public static function isExtendedIndex($extNr)
  {
    return $extNr >= self::RTK_UCS;
  }

  /**
   * Returns true if the index number matches a Heisig frame number.
   *
   * @param int $seqNr A Heisig frame number
   *
   * @return bool True if number is valid RTK/RSH/RTH frame number
   */
  public static function isValidHeisigIndex($seqNr)
  {
    return $seqNr > 0 && $seqNr <= self::inst()->getNumCharacters();
  }

  /**
   * Returns a frame number (ie. Heisig index) for a given utf8 character.
   *
   * @param string $char A single utf8 character
   *
   * @return mixed frame number or false if the char is not in the index
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
   * @param int $ucs
   *
   * @return int extended frame number (Heisig frame number, or UCS)
   */
  public static function getIndexForUCS($ucs)
  {
    $utf = utf8::fromUnicode($ucs);

    $index = rtkIndex::getIndexForChar($utf);

    return $index !== false ? $index : $ucs;
  }

  /**
   * Returns a tf8 character for a given Heisig frame number.
   *
   * Supports extended frame numbers: UCS-2 codes are converted to UTF8 characters.
   *
   * @param int $extNr An "extended frame number" (starts at 1)
   *
   * @return mixed single utf8 character or null if the index number does
   *               not match a heisig character
   */
  public static function getCharForIndex($extNr)
  {
    $id = (int) $extNr;

    if (self::isValidHeisigIndex($id))
    {
      return mb_substr(self::inst()->kanjis, $id - 1, 1, 'utf8');
    }

    if (CJK::isCJKUnifiedUCS($id))
    {
      return utf8::fromUnicode([$id]);
    }

    return null;
  }

  /**
   * Get the UCS code point of the character, given a extended Heisig index.
   *
   * @param int   $n     an extended frame number (Heisig or UCS)
   * @param mixed $extNr
   *
   * @return mixed UCS code point, or false if input is neither a Heisig index
   *               or a valid CJK Unified code point
   */
  public static function getUCSForIndex($extNr)
  {
    $extNr = (int) $extNr;

    // index must be Heisig frame number or valid CJK Unified code point
    $c_utf = rtkIndex::getCharForIndex($extNr);

    return ($c_utf !== null) ? utf8::toCodePoint($c_utf) : false;
  }

  /**
   * Convert one or more extended frame numbers to UCS-2 code value.
   *
   * If the value is already in the CJK range, it is unchanged.
   *
   * @param   int|int[]   Extended frame numbers (index or UCS-2)
   *
   * @return array Array with sequence numbers (ie. Heisig) converted to UCS-2, others unchanged.
   */
  public static function convertToUCS(array $ids)
  {
    $ucsids = [];

    if (!is_array($ids))
    {
      $ids = [$ids];
    }

    for ($i = 0, $n = count($ids); $i < $n; ++$i)
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
          throw new sfException(__METHOD__." Invalid frame number ({$id})");
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
    foreach ($lessons as $k => $v)
    {
      $options[$k] = "Lesson {$k} ({$v} kanji)";
    }

    return $options;
  }

  /**
   * Returns lesson number given a Heisig frame number.
   *
   * @param int $seqNr a Heisig index number (frame number)
   *
   * @return int returns lesson number, 0 if the frame number is not valid
   */
  public static function getLessonForIndex(int $seqNr)
  {
    if (self::isValidHeisigIndex($seqNr))
    {
      $lessons = self::getLessons();
      $maxframe = 0;
      foreach ($lessons as $lesson => $count)
      {
        $maxframe += $count;
        if ($seqNr <= $maxframe)
        {
          return $lesson;
        }
      }
    }

    return 0;
  }

  /**
   * Return information for the lesson, given lesson number.
   *
   * Returns:
   *   - lesson number
   *   - lesson index start
   *   - lesson's kanji count
   *   - position from start of lesson (1 to x) (if $seqNr is provided)
   *
   * @return array|false returns false if index not within current sequence
   */
  public static function getLessonData(int $lessonId, int $seqNr = 0)
  {
    $lessons = self::getLessons();
    $indexEnd = 0;
    foreach ($lessons as $lesson => $count)
    {
      $indexStart = $indexEnd + 1;
      $indexEnd += $count;

      if ($lesson === $lessonId)
      {
        return [
          'lesson_nr' => $lesson,
          'lesson_from' => $indexStart,
          'lesson_pos' => $seqNr ? ($seqNr - $indexStart + 1) : 0,
          'lesson_count' => $count,
        ];
      }
    }

    return false;
  }

  /**
   * Return information for the lesson, given sequence number.
   *
   * See `getLessonData()`.
   *
   * @return array|false returns false if index not within current sequence
   */
  public static function getLessonDataForIndex(int $seqNr)
  {
    $lessonId = self::getLessonForIndex($seqNr);

    return self::getLessonData($lessonId, $seqNr);
  }

  /**
   * Create an array of UCS codes (kanji flashcard ids) for a range of the
   * sequence.
   *
   * @param int  $from    sequence start
   * @param int  $to      sequence end
   * @param bool $shuffle randomize the array
   *
   * @return int[]
   */
  public static function createFlashcardSet($from, $to, $shuffle = false)
  {
    $framenums = range($from, $to);

    if ($shuffle)
    {
      shuffle($framenums);
    }

    $ucsIds = rtkIndex::convertToUCS($framenums);

    return $ucsIds;
  }

  public static function useKeywordsFile()
  {
    // a unique hash for versioning assets cached by client (cf .htaccess rule)
    $HASH = '20220412';

    $sfContext = sfContext::getInstance();

    $seqId = $sfContext->getUser()->getUserSequence();
    $keywordsFile = "/revtk/study/keywords-rtk-{$seqId}.{$HASH}.js";

    $sfContext->getResponse()->addJavascript($keywordsFile, 'first', ['defer' => true]);
  }
}
