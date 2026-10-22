/**
 * Helpers to display the SRS status of kanji cards (lesson map, kanji grid).
 *
 */

const isCardNew = (card: TKanjiCardData) => card.isNew;
const isCardRestudy = (card: TKanjiCardData) => card.box === 1;

export const getColorForCard = (card: TKanjiCardData) => {
  if (!card.box) {
    return "is-off";
  } else if (isCardNew(card)) {
    return "is-new";
  } else if (isCardRestudy(card)) {
    return "is-res";
  }

  const levels = ["", "", "is-L1", "is-L2", "is-L3", "is-L4"];

  return levels[card.box] || "is-L4";
};

export const getTagTextForCard = (card: TKanjiCardData) => {
  const labels = [
    "NOT LEARNED",
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

  if (isCardNew(card)) {
    return "New";
  }

  return labels[card.box] || "-";
};

/**
 * Map SRS card data to the generic data displayed by KoKanjiCard.
 */
export const toKanjiCardItem = (card: TKanjiCardData): TKanjiCardItem => ({
  ucsId: card.ucsId,
  isOn: !!card.box,
  tag: { text: getTagTextForCard(card), css: getColorForCard(card) },
});
