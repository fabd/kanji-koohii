<?php
/**
 * Placeholder for story formatting helpers.
 * 
 */

class rtkStory
{
  // limit for storage (in characters)
  const MAXIMUM_STORY_LENGTH = 512;

  /**
   * Checks that all the kanji links (using {...} notation) are valid and point
   * to an existing character.
   *
   * @return  bool    true, or an error message
   */
  public static function validateKanjiLinks($text)
  {
    $result = preg_match_all('/{([^}]*)}/u', $text, $matches);

    foreach ($matches[1] as $match)
    {
      $valid = true;

      if (ctype_digit($match)) {
        $valid = rtkIndex::isValidHeisigIndex((int)$match) || CJK::isCJKUnifiedUCS((int)$match);
      } else {
        $valid = CJK::isKanjiChar($match);
      }
      
      if (!$valid) {
        return sprintf('The link "{%s}" is not a valid Heisig or CJK Unified Ideograph character/index.', $match);
      }
    }

    return true;
  }

  /**
   * When a story is saved, substitute {n} with {c} where c is the UTF-8
   * character for the kanji/hanzi, and n is the frame number.
   *
   * This is also done for both public and private stories, in case the author
   * selects a different index later and the frame number reference would be
   * incorrect.
   */
  public static function substituteKanjiLinks($text)
  {
    $text = preg_replace_callback('/{([0-9]+)}/', array('self', 'substituteKanjiLinkCallback'), $text);

    return $text;
  }

  public static function substituteKanjiLinkCallback($matches)
  {
    $frameNr = (int)$matches[1]; // frame number or extended (ucs code)
    $kanji = rtkIndex::getCharForIndex($frameNr);

    return sprintf('{%s}', $kanji !== null ? $kanji : $frameNr);
  }
}
