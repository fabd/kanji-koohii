/**
 * Helpers for handling CJK characters (mainly, Japanese unicode blocks).
 *
 * See lib/CJK.php for equivalent functionality on the backend side.
 *
 *
 * EXPORTS
 *
 *   getKanji(text)                    ... filter out all non-kanji from string
 *   getKanjiArray(text)               ... return array of individual kanji in string
 *   getUniqueKanji(text)              ... return array of unique kanji in string
 *
 *   isCharKanji()                     ...
 *   isCJKUnifiedUCS($ucs)             ...
 *
 *   toUnicode()
 *
 *   checkForUnsupportedUtf(text)
 *
 *
 * NOTES
 *   - wanakana doesn't export all utilities like isCharKanji()
 *   - wanakana isCharKanji() doesn't test for "CJK unified ideographs Extension A"
 *
 */

const CJK_UNIFIED_BEGIN = 0x4e00;
const CJK_UNIFIED_END = 0x9faf;

// https://stackoverflow.com/questions/15033196/using-javascript-to-check-whether-a-string-contains-japanese-characters-includi
// const CJK_PUNCTUATION = "\\u3000-\\u303f";
// const CJK_HIRAGANA = "\\u3040-\\u309f";
// const CJK_KATAKANA = "\\u30a0-\\u30ff";
// const CJK_FULLWIDTH = "\\uff00-\\uff9f";

// CJK COMMON + EXTENSION A
//   https://en.wikipedia.org/wiki/CJK_Unified_Ideographs_Extension_A
// const CJK_KANJI = "\\u4e00-\\u9faf\\u3400-\\u4dbf";

export function getKanji(text: string) {
  return getKanjiArray(text).join("");
}

/**
 * Returns an array containing the CJK Unified Ideographs characters filtered
 * from a string. All other characters are ignored.
 */
export function getKanjiArray(text: string) {
  return text.match(/[\u4e00-\u9faf\u3400-\u4dbf]/g) || [];
}

export function getUniqueKanji(text: string) {
  return [...new Set(getKanjiArray(text))];
}

/**
 * Returns true if the string is a single utf8 kanji.
 *
 * Unlike `isCJKUnifiedUCS()`, this function does not require the kanji
 * to be present in the database.
 *
 *  CJK Unified Ideographs (Common and uncommon kanji) U+4E00 - U+9FAF
 *  CJK Unified Ideographs Extension A (Rare kanji)    U+3400 - U+4DBF
 *
 */
export function isCharKanji(char: string) {
  return /[\u4e00-\u9faf\u3400-\u4dbf]/.test(char);
}

/**
 * Checks whether UCS code point is in the "CJK Unified Ideographs" range.
 *
 * (note: to be precise, the range of kanjis that are in the `kanjis` db table,
 *  which is CJK Unified Ideographs (Common and uncommon kanji) U+4E00 - U+9FAF)
 *
 */
export function isCJKUnifiedUCS(ucsId: number): boolean {
  return ucsId >= CJK_UNIFIED_BEGIN && ucsId <= CJK_UNIFIED_END;
}

export function toUnicode(text: string) {
  const chars = getKanjiArray(text);
  let ucsIds = [];
  for (let char of chars) {
    ucsIds.push(char.charCodeAt(0));
  }
  return ucsIds;
}

/**
 * Checks if text contains unicode characters above 0xFFFF, which can not
 * currently be saved to the database (requires `utf8mb4`).
 *
 * @param text
 *
 * @returns array of unique characters, or empty array
 */
export function checkForUnsupportedUtf(text: string) {
  const badChars: string[] = [];
  for (const char of text) {
    if (char.codePointAt(0)! >= 65536) {
      badChars.push(char);
    }
  }

  const uniqueChars = new Set(badChars);
  return [...uniqueChars.values()];
}
