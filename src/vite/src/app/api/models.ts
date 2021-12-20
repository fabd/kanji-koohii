/**
 * Models for the legacy ajax endpoints.
 *
 * Someday with a proper API, we may have:
 *
 *   ./api/models/user.ts
 *   ./api/models/story.ts
 *   etc.
 *
 */

// cf. KanjisPeer::getKanjiByUCS()
export type KanjiData = {
  framenum: number;
  kanji: TKanjiChar;
  ucs_id: TUcsId;
  keyword: string;
  onyomi: string;
  strokecount: number;
};

// TODO : this is 3 api responses combined (dictresults, USER_VOCAB_PICKS, USER_KNOWN_KANJI)
export type GetDictListForUCS = {
  //
  items: DictListEntry[];
  // array of user's selected vocab ([dictid, ...])
  picks: DictId[];
  // string of known kanji (if "reqKnownKanji" is true)
  knownKanji?: string;
};

// a response when caching dict results
export type GetDictCacheFor = {
  //
  items: DictListEntry[];
}

export type PostVoteStoryRequest = {
  request: "star" | "report" | "copy";
  uid: number;
  sid: number;
};

export type PostVoteStoryResponse = {
  storyText?: string; // for copy
  uid: number; // userId -- for vote & report
  sid: number; // ucsId -- for vote & report
  vote: number;
  lastvote: number;
  stars: number;
  kicks: number;
};

export type GetUserStoryResponse = {
  initStoryEdit?: string;
  initStoryPublic?: boolean;
  initFavoriteStory?: boolean; // if true, postStoryView is a "starred" story
};

export type PostUserStoryResponse = {
  // story formatted for display (non-edit mode)
  initStoryView: string;
  // story is currently shared
  isStoryShared: boolean;
  // unique id for the "shared story" added to the page `story-${userId}-${ucsId}`
  sharedStoryId: string;
  // author link in the "shared story" template
  sharedStoryAuthor: string; // html link
};
