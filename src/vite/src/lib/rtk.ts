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
 *   getSeqKanjis()              ... return string of all kanji in current sequence
 *                                   (ie. OLD/NEW edition) in index order so that the
 *                                   position in the string is the sequence index (-1)
 *
 *   getSeqName()                ... returns the sequence's name (ie. "Old/New Edition")
 *   getSeqLessons()             ... return Map of the lessons in the sequence
 *
 *   isValidSequenceIndex(seqNr)
 *
 *   getIndexForUCS(ucsId)
 *   getUCSForIndex(seqNr)
 *
 *   getKeywordForUCS(ucsId)
 *
 *   getCharForIndex(extNr)
 *
 *   filterRtkKanji(chars)  Removes all non-RTK sequence characters from the chars array
 *
 *
 */

import { kk_globals_get, kk_globals_has } from "@app/root-bundle";
import * as CJK from "@/lib/cjk";

type TKeywordMap = Map<TUcsId, string>; // UCS code, keyword
type TMapUcsToIndex = Map<TUcsId, number>;
type TMapIndexToUcs = Map<number, TUcsId>;

// initiliazed as needed
let kkGlobalsSeqKanjis: string;

// the lessons Map is created when needed
let seqLessonsMap: TSeqLessonMap;

// maps to convert between UCS code and Heisig index (initialized by helpers as needed)
let rtkUcsToIndexMap: TMapUcsToIndex;
let rtkIndexToUcsMap: TMapIndexToUcs;

let origKeywordsMap: Map<number, string>; // <sequence nr, keyword>
let userKeywordsMap: TKeywordMap;

export function getSeqKanjis(): string {
  if (!kkGlobalsSeqKanjis) {
    kkGlobalsSeqKanjis = kk_globals_get("SEQ_KANJIS") as string;
  }

  return kkGlobalsSeqKanjis;
}

export function getSeqName(): string {
  console.assert(kk_globals_has("SEQ_LESSONS"));
  return kk_globals_get("SEQ_LESSONS").sequenceName;
}

export function getSeqLessons() {
  console.assert(kk_globals_has("SEQ_LESSONS"));
  return (seqLessonsMap ??= new Map(kk_globals_get("SEQ_LESSONS").lessons));
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
export function getUCSForIndex(seqNr: number): number | false {
  return getIndexToUcsMap().get(seqNr) || false;
}

export function getKeywordForUCS(ucsId: TUcsId) {
  const userKeywords = userKeywordsMap || getUserKeywords();
  const origKeywords = origKeywordsMap || getOrigKeywords();

  return (
    userKeywords.get(ucsId) || origKeywords.get(getIndexForUCS(ucsId)) || "-"
  );
}

/**
 * Returns true if the index is within the kanji sequence (ie. Heisig index).\
 *
 * Same as (for now):
 *
 *   return (seqNr > 0 && seqNr <= getSeqKanjis().length);
 *
 */
export function isValidSequenceIndex(seqNr: number): boolean {
  return getIndexToUcsMap().has(seqNr);
}

/**
 * Returns CJK character for given "extended frame number" (Heisig or UCS).
 *
 */
export function getCharForIndex(extNr: number): string | null {
  if (isValidSequenceIndex(extNr)) {
    return getSeqKanjis().charAt(extNr - 1);
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
