import { kk_globals_get } from "@/app/common/root-bundle";
import * as RTK from "@/lib/rtk";

type TUserKanjiMap = Map<TUcsId, TUserKanjiCard>;

let userKanji: TUserKanjiMap;

/**
 * Creates a map of the user's flashcard data.
 *
 * Note that the kanji cards does NOT necessarily include all the user's cards,
 * it may be eg. only the cards shown for the current lesson on the homepage dashboard.
 *
 * @see ReviewsPeer::getUserKanjiCardsJS()
 */
export function getUserKanji() {
  userKanji ??= new Map(kk_globals_get("USER_KANJI_CARDS"));

  return userKanji;
}
