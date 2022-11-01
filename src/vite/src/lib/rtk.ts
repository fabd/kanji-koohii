/**
 * Helpers to deal with handling kanji sequences (ie. RTK indexes).
 *
 * Helpers:
 *   filterRtkKanji()
 * 
 *   getIndexForUCS()
 *   getKeywordForUCS()
 *
 */

import { kk_globals_get, kk_globals_has } from "@app/root-bundle";

// UCS code, keyword
type TKeywordMap = Map<TUcsId, string>;

// UCS code, Heisig Index
type TRtkIndexMap = Map<TUcsId, number>;

let rtkIndexMap: TRtkIndexMap;
let origKeywordsMap: Map<number, string>; // <sequence nr, keyword>
let userKeywordsMap: TKeywordMap;

/**
 * Derive index of <ucs, seq_nr> from the KANJIS string which is already
 * included in the keywords file used by the Study pages.
 *
 */
function getRtkIndexMap() {
  const chars: string = kk_globals_get("SEQ_KANJIS");
  rtkIndexMap = new Map(chars.split("").map((k, i) => [k.charCodeAt(0), i + 1]));

  return rtkIndexMap;
}

const getUserKeywords = function () {
  userKeywordsMap ??= new Map(kk_globals_get("USER_KEYWORDS_MAP")) as TKeywordMap;

  return userKeywordsMap;
};

/**
 * Create a map <seq_nr, keyword> by re-using the data from the keywords
 * file which is also used on the Study pages.
 *
 * NOTES
 *   - the keywords file is OPTIONAL, and may not be present
 *   - the map's keys are sequence number, not UCS
 */
const getOrigKeywords = function () {
  const keywords: string[] = (kk_globals_has("SEQ_KEYWORDS") && kk_globals_get("SEQ_KEYWORDS")) || [];

  origKeywordsMap ??= new Map(keywords.map((str, index) => [index + 1, str]));

  return origKeywordsMap;
};

/**
 * Returns a Heisig index number for UCS codes that match a Heisig char.
 *
 * If the char is not within active sequence, returns the code as is.
 *
 * @return number ... extended frame number (Heisig index, or UCS)
 */
function getIndexForUCS(ucsId: TUcsId): number {
  return (rtkIndexMap || getRtkIndexMap()).get(ucsId) || ucsId;
}

function getKeywordForUCS(ucsId: TUcsId) {
  const userKeywords = userKeywordsMap || getUserKeywords();
  const origKeywords = origKeywordsMap || getOrigKeywords();

  return userKeywords.get(ucsId) || origKeywords.get(getIndexForUCS(ucsId)) || "-";
}

/**
 * Removes all non-RTK sequence characters from the chars array.
 * 
 * @param string[] chars ... an array of characters (eg. from `split('')`)
 * 
 */
function filterRtkKanji(chars: string[]): string[] {
  return chars.filter(
    (char: string) => { return getIndexForUCS(char.codePointAt(0) || 0); }
  );
}

export { getKeywordForUCS, getIndexForUCS, filterRtkKanji };
