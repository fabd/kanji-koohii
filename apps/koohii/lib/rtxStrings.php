<?php

/**
 * Currently loaded only to map Japanese to Chinese strings in the templates.
 *
 * The key for the strings is the original RevTK string, so  for Japanese mode,
 * the "key" string is returned as is.
 */

$GLOBALS['CJ_STRINGS'] = array(
  'Japanese'               => 'Chinese',
  'kanji'                  => 'hanzi',
  'Onyomi'                 => 'Pinyin',
  'Remembering the Kanji'  => 'Remembering Hanzi',
  'Kanji Koohii!'          => 'Reviewing the Hanzi',   // website name
  'RTK'                    => 'RTH'
);

/**
 * Placeholder class to make use of the autoload feature.
 */
class rtxStrings
{
  public static function init()
  {
  }
}

