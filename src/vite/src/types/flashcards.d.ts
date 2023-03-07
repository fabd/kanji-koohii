/**
 * These are types for the old flashcard review code that was refactored
 *  for Vite build. Hence they are all grouped here.
 *
 */

type TCardData = {
  framenum: number;
  id: TUcsId;
  kanji: TKanjiChar;
  keyword: string; // html for <a>
  strokecount: number;
  isAgain?: boolean; // added during review, whether card is being repeated
};

type TVocabCardData = {
  dispword: string; // html of compound with hyperlinked (<a>) kanji
  compound: string;
  reading: string;
  glossary: string;
  id: TUcsId;
};

// callback signature for action handler
type TReviewPageActionFn = (actionId: string, event: Event) => boolean;

// props for KanjiReview/VocabReview instance (to be a Vue component)
type TKanjiReviewProps = {
  end_url: string;
  editstory_url: string;
  freemode: boolean;
};
type TVocabReviewProps = {
  back_url: string;
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

// make sure this matches RATE_* in FlashcardReview.php
type TReviewRating =
  | "no"
  | "again"
  | "again-hard"
  | "again-yes"
  | "again-easy"
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
