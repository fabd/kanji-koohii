/**
 * Helpers for handling Japanese text.
 *
 * EXPORTS
 *
 *   getKanji()                        ...
 *   getKanjiArray()                   ...
 *   getUniqueKanji()                  ...
 *
 *   isCharKanji()                     ...
 *
 *   toUnicode()
 *
 *
 * NOTES
 *   - wanakana doesn't export all utilities like isCharKanji()
 *   - wanakana isCharKanji() doesn't test for "CJK unified ideographs Extension A"
 *
 */

// https://stackoverflow.com/questions/15033196/using-javascript-to-check-whether-a-string-contains-japanese-characters-includi
// const CJK_PUNCTUATION = "\\u3000-\\u303f";
// const CJK_HIRAGANA = "\\u3040-\\u309f";
// const CJK_KATAKANA = "\\u30a0-\\u30ff";
// const CJK_FULLWIDTH = "\\uff00-\\uff9f";

// CJK COMMON + EXTENSION A
//   https://en.wikipedia.org/wiki/CJK_Unified_Ideographs_Extension_A
// const CJK_KANJI = "\\u4e00-\\u9faf\\u3400-\\u4dbf";

export function getKanjiArray(text: string) {
  return text.match(/[\u4e00-\u9faf\u3400-\u4dbf]/g) || [];
}

export function getKanji(text: string) {
  return getKanjiArray(text).join("");
}

export function getUniqueKanji(text: string) {
  return [...new Set(getKanjiArray(text))];
}

export function isCharKanji(char: string) {
  return /[\u4e00-\u9faf\u3400-\u4dbf]/.test(char);
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
  let badChars: string[] = [];
  for (let char of text) {
    if (char.codePointAt(0)! >= 65536) {
      badChars.push(char);
    }
  }

  const uniqueChars = new Set(badChars);
  return [...uniqueChars.values()];
}
