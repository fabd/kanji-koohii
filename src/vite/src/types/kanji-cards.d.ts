// kanji card data as hydrated from php, see getUserKanjiCardsJS()
type TUserKanjiCard = {
  ucs: TUcsId;
  box: number;
  new: number;
};

// generic kanji card data, as displayed by KoKanjiCard
type TKanjiCardItem = {
  ucsId: TUcsId;
  // SRS box, 0 means no flashcard
  box: number;
  // optional label at the bottom of the card, eg. { text: "Fail", css: "is-fail" }
  tag: { text: string; css: string };
};
