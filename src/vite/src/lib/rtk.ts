/**
 * This modules deals with handling kanji sequences (ie. RTK indexes).
 *
 * Methods:
 *
 *   getIndexForUCS()
 *   getKeywordForUCS()
 *
 */
import { kk_globals_get } from "@app/root-bundle";

type TKeywordMap = Map<TUcsId, string>; // UCS code, keyword
type TRtkIndexMap = Map<TUcsId, number>; // UCS code, Heisig Index

let rtkIndexMap: TRtkIndexMap;

let origKeywordsMap: TKeywordMap;
let userKeywordsMap: TKeywordMap;

function getRtkIndexMap() {
  // a string of all kanjis in active sequence, in sequential order (no gaps)
  // -- therefore sequence nr is inferred from position in string
  const chars: string = kk_globals_get("SEQ_KANJIS");
  rtkIndexMap = new Map(chars.split("").map((k, i) => [k.charCodeAt(0), i + 1]));

  return rtkIndexMap;
}

const getUserKeywords = function () {
  userKeywordsMap ??= new Map(kk_globals_get("USER_KEYWORDS_MAP")) as TKeywordMap;

  return userKeywordsMap;
};

/**
 * For now, orig keywords is OPTIONAL. If not set by php, then it is assumed
 * all necessary keywords would be in the user keywords map.
 *
 * Cf. "sightreading" page where coalesced keywords are returned in USER_KEYWORDS_MAP.
 */
const getOrigKeywords = function () {
  // keywords file should be included in the page (from the php side)
  // console.assert(window.KK && window.KK["SEQ_KEYWORDS"].length);
  const keywords: string[] = (window.KK["SEQ_KEYWORDS"] && kk_globals_get("SEQ_KEYWORDS")) || [];
  origKeywordsMap ??= new Map(keywords.map((str, index) => [index, str]));

  return origKeywordsMap;
};

// return a sequence number (starts at 1), 0 if ucs code not in active sequence
function getIndexForUCS(ucsId: TUcsId): number {
  return (rtkIndexMap || getRtkIndexMap()).get(ucsId) || 0;
}

function getKeywordForUCS(ucsId: TUcsId) {
  const userKeywords = userKeywordsMap || getUserKeywords();
  const origKeywords = origKeywordsMap || getOrigKeywords();

  return userKeywords.get(ucsId) || origKeywords.get(ucsId) || "-";
}

export { getKeywordForUCS, getIndexForUCS };
