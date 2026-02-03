import { kk_globals_get } from "@app/root-bundle";
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
  userKanji ??= new Map(kk_globals_get("USER_KANJI_CARDS")) as TUserKanjiMap;

  return userKanji;
}

/**
 * Create a range of flashcards as used by dumb components, eg. a kanji grid.
 *
 *   - merge the user's flashcard data where available
 *   - set all other cards to an empty state (ie. "Not learned")
 *
 */
export function getKanjiCardDataForRange(
  from: number,
  to: number
): TKanjiCardData[] {
  const cards: TKanjiCardData[] = [];
  const userKanji = getUserKanji();

  for (let index = from; index <= to; index++) {
    const ucsId = RTK.getUCSForIndex(index) as number;
    const userCard = userKanji.get(ucsId);

    cards.push({
      ucsId: ucsId,
      box: (userCard && userCard.box) || 0,
      isNew: (userCard && userCard.new > 0) || false,
    });
  }

  return cards;
}
