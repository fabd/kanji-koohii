/**
 * Collect all the API calls in one place, decouples ux code from KoohiiRequest
 *
 * import { KoohiiAPI, TRON } from '../lib/KoohiiAPI.js'
 *
 */

import KoohiiRequest, { KoohiiRequestHandlers } from "@lib/KoohiiRequest";
import * as TRON from "@lib/koohii/tron";

export type DictId = number;

export interface DictListEntry {
  id: DictId; // jdict.id
  c: string; // compound
  r: string; // reading
  g: string; // glossary
  pri: number; // jdict.pri (bitfield)

  // FIXME? refactor to use a separate hash for DictList templating
  known?: boolean; // (client side) true if user knows all kanji in this compound
  fr?: string; // formatted reading
  pick?: boolean; // selected state
}

// cf. KanjisPeer::getKanjiByUCS()
export interface KanjiData {
  framenum: number;
  kanji: string;
  ucs_id: number;
  keyword: string;
  onyomi: string;
  strokecount: number;
}

/**
 *   formattedStory   string
 *
 *   isFavoriteStory  boolean
 *   isStoryShared    boolean     ... story is currently shared
 *
 *   sharedStoryId    string      ... unique id for the "shared story" added to the page
 *   profileLink      string      ... author link in the "shared story" template
 */
export interface KoohiiApiPostUserStoryResponse {
  //
  postStoryView: string; // story formatted for display (non-edit mode)

  // GET
  postStoryEdit?: string;
  postStoryPublic?: boolean;
  isFavoriteStory?: boolean; // if true, postStoryView is a "starred" story

  // POST
  // for visual feedback, adding or removing the story from Shared Stories list
  isStoryShared: boolean;
  sharedStoryId: string; // `story-${userId}-${ucsId}`
  sharedStoryAuthor: string; // html link
}

// export interface KoohiiApiSetVocabForCardResponse {}


export interface KoohiiApiGetDictListForUCSResponse {
  //
  items: DictListEntry[];
  // array of user's selected vocab ([dictid, ...])
  picks: DictId[];
  // string of known kanji (if "req_known_kanji" is true)
  known_kanji?: string;
}

export interface KoohiiAPIInterface {
  postUserStory: (
    params: {
      ucsId: number;
      txtStory: string;
      isPublic: boolean;
      reviewMode: boolean;
    },
    handlers: KoohiiRequestHandlers<KoohiiApiPostUserStoryResponse>
  ) => void;

  getDictListForUCS: (
    params: {
      ucsId: number;
      getKnownKanji: boolean;
    },
    handlers: KoohiiRequestHandlers<KoohiiApiGetDictListForUCSResponse>
  ) => void;

  setVocabForCard: (
    params: {
      ucs: number;
      dictid: number;
    },
    handlers: KoohiiRequestHandlers<{}>
  ) => void;

  deleteVocabForCard: (
    params: {
      ucs: number;
    },
    handlers: KoohiiRequestHandlers<{}>
  ) => void;
}

export const KoohiiAPI: KoohiiAPIInterface = {
  /**
   *
   * @param {object} storyInfo   { number, string, boolean, boolean }
   * @param {object} handlers    then & error handlers (cf. KoohiiRequest)
   */
  postUserStory({ ucsId, txtStory, isPublic, reviewMode }, handlers) {
    let data = {
      ucs_code: ucsId,
      reviewMode: reviewMode,
      postStoryEdit: txtStory,
      postStoryPublic: isPublic,
    };

    KoohiiRequest.request<KoohiiApiPostUserStoryResponse>(
      "/study/editstory",
      { method: "post", data: data },
      handlers
    );
  },

  //
  //  Shared Stories   star / report / copy
  //
  /*
  ajaxSharedStory(data, handlers) {
    KoohiiRequest.request(
      "/study/ajax",
      { method: "post", data: data },
      handlers
    );
  },
  */

  //  DictList component

  // get vocab entries for the Dictionary (example words for given kanji)
  getDictListForUCS({ ucsId, getKnownKanji }, handlers) {
    let data = { ucs: ucsId, req_known_kanji: getKnownKanji };
    KoohiiRequest.request(
      "/study/dict",
      { method: "get", params: data },
      handlers
    );
  },

  //  vocabpicks

  setVocabForCard({ ucs, dictid }, handlers) {
    let data = { ucs: ucs, dictid: dictid };
    KoohiiRequest.request(
      "/study/vocabpick",
      { method: "post", data: data },
      handlers
    );
  },

  deleteVocabForCard({ ucs }, handlers) {
    let data = { ucs };
    KoohiiRequest.request(
      "/study/vocabdelete",
      { method: "post", data: data },
      handlers
    );
  },
};

export { TRON };
