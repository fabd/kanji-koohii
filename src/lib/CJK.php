<?php
/**
 * CJK contains helpers for conversion between katakana/hiragana, hankakau,
 * and working with kanji, all in utf8 strings.
 *
 * References:
 *   [Kanji codes](http://www.rikai.com/library/kanjitables/kanji_codes.unicode.shtml)
 *   Regular Expressions Cookbook: "Unicode Ranges and Scripts"
 *
 * Public methods:
 *
 *  normalizeFullWidthRomanCharacters($u8s)
 *  hasKanji($s)
 *  getKanji($s)
 *
 *  isKanjiChar($char)              Check if character is within "CJK unified ideographs" (not kana)
 *  isCJKUnifiedUCS($ucs)
 *  isHiraUCS($ucs)
 *  isKataUCS($ucs)
 *
 *  toHiraganaUCS($ucs_array)
 *  toKatakanaUCS($ucs_array)
 *
 *  toHiragana($u8s)
 *  toKatakana($u8s)
 *
 *
 * Reference info:
 *  CJK Unified Ideographs (Common and uncommon kanji) U+4E00 - U+9FAF
 *  CJK Unified Ideographs Extension A (Rare kanji)    U+3400 - U+4DBF
 */
class CJK
{
  /**
   * "CJK unifed ideographs - Common and Uncommon Kanji".
   */
  public const CJK_UNIFIED_BEGIN = 0x4E00;
  public const CJK_UNIFIED_END   = 0x9FAF;

  /**
   * Regular expression to match the range above. All characters within this
   * range should exist in RevTK database.
   */
  public const PREG_CJK_COMMON = '\x{4e00}-\x{9faf}';

  /**
   * Regular expression to match range for "CJK unified ideographs Extension A
   * - Rare kanji".
   */
  public const PREG_CJK_EXTENA = '\x{3400}-\x{4dbf}';

  /**
   * Convert full-width Japanese Roman characters to ASCII roman characters.
   *
   * This helps the user not having to shift out of the Japanese input mode to write numbers.
   *
   * Note: not thoroughly tested beyond the digits (0-9)
   *
   * @param string $u8s Utf8 string
   *
   * @return string Utf8 string
   */
  public static function normalizeFullWidthRomanCharacters($u8s)
  {
    $aUCS = utf8::toUnicode($u8s);
    for ($i = 0; $i < count($aUCS); $i++) {
      if ($aUCS[$i] >= 0xFF10 && $aUCS[$i] <= 0xFF5A) {
        $aUCS[$i] = $aUCS[$i] - 0xFF00 + 32;
      }
    }

    return utf8::fromUnicode($aUCS);
  }

  /**
   * Returns true if the string contains CJK Unified Ideographs (ie. kanji).
   *
   * @param string $s String in utf8 or ascii
   *
   * @return bool true if string contains at least one CJK Unified Ideograph
   */
  public static function hasKanji($s)
  {
    return preg_match('/['.self::PREG_CJK_COMMON.']/u', $s);
  }

  /**
   * Returns true if the string is a single utf8 kanji.
   *
   * @param string $char a single kanji character in utf-8 encoding
   *
   * @return bool
   */
  public static function isKanjiChar($char)
  {
    return is_string($char) && (1 === preg_match('/^['.self::PREG_CJK_COMMON.']$/u', $char));
  }

  /**
   * Checks whether UCS code value is in the "CJK Unified Ideographs" range.
   * (note: to be precise, the range that is defined by the constants herein,
   * which matches what characters are in the database).
   *
   * @param int $ucs UCS code value as integer
   *
   * @return bool
   */
  public static function isCJKUnifiedUCS($ucs)
  {
    // CJK unifed ideographs - Common and uncommon kanji (4e00 - 9faf)
    return is_int($ucs) && $ucs >= self::CJK_UNIFIED_BEGIN && $ucs <= self::CJK_UNIFIED_END;
  }

  /**
   * Returns an array containing the CJK Unified Ideographs characters filtered
   * from a string. All other characters are ignored.
   *
   * @param string $s Utf8 or ascii string
   *
   * @return array Array of kanji characters, empty array if non found
   */
  public static function getKanji($s)
  {
    $result = preg_match_all('/['.self::PREG_CJK_COMMON.']/u', $s, $matches);

    return $result ? $matches[0] : [];
  }

  /**
   * Check if UCS-2 code is in Hiragana range ( 3040 - 309f ).
   *
   * @param int $ucs UCS code value
   *
   * @return bool
   */
  public static function isHiraUCS($ucs)
  {
    return $ucs >= 0x3040 && $ucs <= 0x309F;
  }

  /**
   * Check if UCS-2 code is in Katakana range ( 30a0 - 30ff).
   *
   * @param int $ucs UCS code value as integer
   *
   * @return bool
   */
  public static function isKataUCS($ucs)
  {
    return $ucs >= 0x30A0 && $ucs <= 0x30FF;
  }

  // convert Katakana in unicode array to Hiragana
  public static function toHiraganaUCS(array $ua_text)
  {
    // Hiragana ( 3040 - 309f)  Katakana ( 30a0 - 30ff)
    for ($i = 0; $i < count($ua_text); $i++) {
      if ($ua_text[$i] >= 0x30A0 && $ua_text[$i] <= 0x30FF) {
        $ua_text[$i] -= 0x0060;
      }
    }

    return $ua_text;
  }

  // convert Katakana in string to Hiragana
  public static function toHiragana($u8s)
  {
    $ua_text = utf8::toUnicode($u8s);

    $ua_text = self::toHiraganaUCS($ua_text);

    return utf8::fromUnicode($ua_text);
  }

  // convert Hiragana in unicode array to Katakana
  public static function toKatakanaUCS(array $ua_text)
  {
    // Hiragana ( 3040 - 309f)  Katakana ( 30a0 - 30ff)
    for ($i = 0; $i < count($ua_text); $i++) {
      if ($ua_text[$i] >= 0x3040 && $ua_text[$i] <= 0x309F) {
        $ua_text[$i] += 0x0060;
      }
    }

    return $ua_text;
  }

  // convert Hiragana in string to Katakana
  public static function toKatakana($u8s)
  {
    $ua_text = utf8::toUnicode($u8s);

    $ua_text = self::toKatakanaUCS($ua_text);

    return utf8::fromUnicode($ua_text);
  }
}
