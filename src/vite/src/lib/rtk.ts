/**
 * Helpers that deal with the kanji sequences (ie. Heisig's 5th/6th editions).
 *
 * And also helpers that handle the associated keywords.
 *
 * These JS helpers correspond to the functionality of rtkIndex.php.
 *
 *
 * EXPORTS
 *
 *   getIndexForUCS(ucsId)
 *   getKeywordForUCS(ucsId)
 *
 *   getUCSForIndex(extNr)
 *   getCharForIndex(extNr)
 *
 *   filterRtkKanji()
 *
 */

import { kk_globals_get, kk_globals_has } from "@app/root-bundle";
import * as CJK from "@/lib/cjk";

type TKeywordMap = Map<TUcsId, string>; // UCS code, keyword
type TMapUcsToIndex = Map<TUcsId, number>;
type TMapIndexToUcs = Map<number, TUcsId>;

// initiliazed as needed
let kkGlobalsSeqKanjis: string;

// maps to convert between UCS code and Heisig index (initialized by helpers as needed)
let rtkUcsToIndexMap: TMapUcsToIndex;
let rtkIndexToUcsMap: TMapIndexToUcs;

let origKeywordsMap: Map<number, string>; // <sequence nr, keyword>
let userKeywordsMap: TKeywordMap;

function getSeqKanjis(): string {
  if (!kkGlobalsSeqKanjis) {
    kkGlobalsSeqKanjis = kk_globals_get("SEQ_KANJIS") as string;
  }

  return kkGlobalsSeqKanjis;
}

/**
 * Derive index of <ucs, seq_nr> from the KANJIS string which is already
 * included in the keywords file used by the Study pages.
 *
 */
function getUcsToIndexMap(): TMapUcsToIndex {
  if (!rtkUcsToIndexMap) {
    const chars = getSeqKanjis();

    rtkUcsToIndexMap = new Map(
      chars.split("").map((k, i) => [k.charCodeAt(0), i + 1])
    );
  }

  return rtkUcsToIndexMap;
}

function getIndexToUcsMap(): TMapIndexToUcs {
  if (!rtkIndexToUcsMap) {
    const chars = getSeqKanjis();

    rtkIndexToUcsMap = new Map(
      chars.split("").map((k, i) => [i + 1, k.charCodeAt(0)])
    );
  }

  return rtkIndexToUcsMap;
}

const getUserKeywords = function () {
  userKeywordsMap ??= new Map(
    kk_globals_get("USER_KEYWORDS_MAP")
  ) as TKeywordMap;

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
  const keywords: string[] =
    (kk_globals_has("SEQ_KEYWORDS") && kk_globals_get("SEQ_KEYWORDS")) || [];

  origKeywordsMap ??= new Map(keywords.map((str, index) => [index + 1, str]));

  return origKeywordsMap;
};

/**
 * Returns a Heisig index number for UCS codes that match a Heisig char.
 * If the char is not within active sequence, returns the code as is.
 *
 * Alias: "get extended frame number".
 *
 * @return number ... extended frame number (Heisig index, or UCS)
 */
export function getIndexForUCS(ucsId: TUcsId): number {
  return getUcsToIndexMap().get(ucsId) || ucsId;
}

/**
 * Returns UCS code for Heisig index. If the index is not within the active
 * kanji sequence (eg. RTK 6th edition), returns false.
 *
 */
export function getUCSForIndex(extNr: number): number | false {
  return getIndexToUcsMap().get(extNr) || false;
}

export function getKeywordForUCS(ucsId: TUcsId) {
  const userKeywords = userKeywordsMap || getUserKeywords();
  const origKeywords = origKeywordsMap || getOrigKeywords();

  return (
    userKeywords.get(ucsId) || origKeywords.get(getIndexForUCS(ucsId)) || "-"
  );
}

/**
 * Returns CJK character for given "extended frame number" (Heisig or UCS).
 *
 */
export function getCharForIndex(extNr: number): string | null {
  let heisigNr = getUcsToIndexMap().get(extNr);

  if (heisigNr) {
    return getSeqKanjis().charAt(heisigNr - 1);
  }

  if (CJK.isCJKUnifiedUCS(extNr)) {
    return String.fromCodePoint(extNr);
  }

  return null;
}

/**
 * Removes all non-RTK sequence characters from the chars array.
 *
 * @param string[] chars ... an array of characters (eg. from `split('')`)
 *
 */
export function filterRtkKanji(chars: string[]): string[] {
  return chars.filter((char: string) => {
    return getIndexForUCS(char.codePointAt(0) || 0);
  });
}
