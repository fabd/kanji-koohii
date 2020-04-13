/**
 * Collect all the API calls in one place, decouples ux code from KoohiiRequest
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

export interface KoohiiApiPostUserStoryResponse {
  //
  postStoryView: string; // story formatted for display (non-edit mode)

  // GET
  postStoryEdit?: string;
  postStoryPublic?: boolean;
  // // if true, postStoryView is a "starred" story
  isFavoriteStory?: boolean;

  // POST
  // for visual feedback, adding or removing the story from Shared Stories list
  //
  // story is currently shared
  isStoryShared: boolean;
  // unique id for the "shared story" added to the page `story-${userId}-${ucsId}`
  sharedStoryId: string;
  // author link in the "shared story" template
  sharedStoryAuthor: string; // html link
}

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

// base url to account for dev/test envs, no trailing slash
const apiBaseUrl = () => {
  return window.App.KK_BASE_URL.replace(/\/$/, '');
}

const apiUrlForPath = (path: string) => apiBaseUrl() + path;

export const KoohiiAPI: KoohiiAPIInterface = {
  postUserStory({ ucsId, txtStory, isPublic, reviewMode }, handlers) {
    let data = {
      ucs_code: ucsId,
      reviewMode: reviewMode,
      postStoryEdit: txtStory,
      postStoryPublic: isPublic,
    };

    KoohiiRequest.request<KoohiiApiPostUserStoryResponse>(
      apiUrlForPath("/study/editstory"),
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
      apiUrlForPath("/study/dict"),
      { method: "get", params: data },
      handlers
    );
  },

  //  vocabpicks

  setVocabForCard({ ucs, dictid }, handlers) {
    let data = { ucs: ucs, dictid: dictid };
    KoohiiRequest.request(
      apiUrlForPath("/study/vocabpick"),
      { method: "post", data: data },
      handlers
    );
  },

  deleteVocabForCard({ ucs }, handlers) {
    let data = { ucs };
    KoohiiRequest.request(
      apiUrlForPath("/study/vocabdelete"),
      { method: "post", data: data },
      handlers
    );
  },
};

export { TRON };
