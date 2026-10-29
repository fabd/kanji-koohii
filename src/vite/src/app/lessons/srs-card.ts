/**
 * Helpers to display the SRS status of kanji cards (lesson map, kanji grid).
 *
 */
import * as RTK from "@/lib/rtk";
import { getUserKanji } from "@/lib/user";

export const getColorForCard = (card?: TUserKanjiCard) => {
  if (!card) {
    return "is-off";
  } else if (card.new) {
    return "is-new";
  } else if (card.box === 1) {
    return "is-res";
  }

  const levels = ["", "", "is-L1", "is-L2", "is-L3", "is-L4"];

  return levels[card.box] || "is-L4";
};

export const getTagTextForCard = (card?: TUserKanjiCard) => {
  const labels = [
    "",
    "Restudy",
    "Box 1",
    "Box 2",
    "Box 3",
    "Box 4",
    "Box 5",
    "Box 6",
    "Box 7",
    "Box 8",
    "Box 9",
    "Box 10",
  ];

  if (card && card.new) {
    return "New";
  } else if (card) {
    return labels[card.box] as string;
  }

  return "NOT LEARNED";
};

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
): TKanjiCardItem[] {
  const cards: TKanjiCardItem[] = [];
  const userKanji = getUserKanji();

  for (let index = from; index <= to; index++) {
    const ucsId = RTK.getUCSForIndex(index) as number;
    const userCard = userKanji.get(ucsId);

    cards.push({
      ucsId: ucsId,
      box: (userCard && userCard.box) || 0,
      tag: { text: getTagTextForCard(userCard), css: getColorForCard(userCard) },
    });
  }

  return cards;
}
