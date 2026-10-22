// kanji card data as hydrated from php, see getUserKanjiCardsJS()
type TUserKanjiCard = {
  ucs: TUcsId;
  box: number;
  new: number;
};

// kanji card data, as used by the components
type TKanjiCardData = {
  ucsId: TUcsId;
  box: number;
  isNew: boolean;
};

// generic kanji card data, as displayed by KoKanjiCard
type TKanjiCardItem = {
  ucsId: TUcsId;
  // a flashcard exists for this kanji (filled card background vs empty with dashed border)
  isOn: boolean;
  // optional label at the bottom of the card, eg. { text: "Fail", css: "is-fail" }
  tag?: { text: string; css: string };
};
