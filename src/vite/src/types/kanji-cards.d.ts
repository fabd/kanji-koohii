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
