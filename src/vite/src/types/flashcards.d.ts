type TCardData = {
  framenum: number;
  id: TUcsId;
  kanji: TKanjiChar;
  keyword: string; // html for <a>
  strokecount: number;
};

type TVocabCardData = {
  dispword: string; // html of compound with hyperlinked (<a>) kanji
  compound: string;
  reading: string;
  glossary: string;
  id: TUcsId;
};

// review rating code (client/server)
type TReviewRating =
  | 1 // No
  | 2 // Yes
  | 3 // Easy
  | "again" // Again (repeat card)
  | "h" // Hard
  | 4 // Delete card
  | 5; // Skip card

type TCardAnswer = {
  // the kanji id (UCS code) acts as the flashcard's unique id
  id: TUcsId;
  // the flashcard answer, including actions like "skip" and "delete"
  r: TReviewRating;
};
