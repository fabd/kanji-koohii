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

// @see FlashcardReview.js
type TReviewOptions = {
  items: TUcsId[];
  ajax_url: string;
  back_url?: string;
  params?: Dictionary;
  max_undo?: number;
  events: {
    /* eslint-disable-next-line @typescript-eslint/ban-types */
    [name: string]: Function;
  };
  scope: any;
  put_request?: boolean;
};

// make sure this matches RATE_* in uiFlashcardReview.php
type TReviewRating =
  | "no"
  | "again"
  | "hard"
  | "yes"
  | "easy"
  | "delete"
  | "skip";

type TCardAnswer = {
  // the kanji id (UCS code) acts as the flashcard's unique id
  id: TUcsId;
  // the flashcard answer, including actions like "skip" and "delete"
  r: TReviewRating;
};

type TReviewSyncRequest = {
  // an array of unique flashcard ids, requesting data
  get?: TUcsId[];
  //
  opt?: any;
  //
  put?: TCardAnswer[];
};

type TReviewSyncResponse = {
  // an array of flashcard data
  get: TCardData[];
  // the ids of items that were succesfully updated server side
  put: TUcsId[];
};
