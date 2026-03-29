import { baseUrl } from "@/lib/koohii";
import { HttpClient } from "./http-client";
import type {
  GetDictListForUCS,
  GetDictCacheFor,
  GetEditFlashcardResponse,
  PostEditFlashcardResponse,
  EditStoryResponse,
  PostUserStoryResponse,
  PostUserKeywordResponse,
  PostVoteStoryRequest,
  PostVoteStoryResponse,
} from "./models";

/**
 * A singleton class api for the legacy ajax endpoints,
 *  it uses HttpClient, so it always resolves to a TRON message.
 */
export class LegacyApi extends HttpClient {
  private static instance?: LegacyApi;

  constructor(baseUrl: string) {
    super(baseUrl);
  }

  static getInstance(baseUrl: string) {
    if (!this.instance) {
      this.instance = new LegacyApi(baseUrl);
    }

    return this.instance;
  }

  /*getEditFlashcard(ucsId: TUcsId) {
    return this.get<GetEditFlashcardResponse>("/flashcards/edit", {
      ucs: ucsId,
    });
  }*/

  postEditFlashcard(ucsId: number, action: "delete" | "restudy") {
    return this.post<PostEditFlashcardResponse>("/flashcards/edit", {
      ucs: ucsId,
      action,
    });
  }

  /*getEditStory(ucsCode: TUcsId, reviewMode: boolean) {
    return this.get<EditStoryResponse>("/study/editstory", {
      ucsCode,
      reviewMode,
    });
  }*/

  postUserStory(
    ucsId: number,
    txtStory: string,
    isPublic: boolean,
    reviewMode: boolean
  ) {
    return this.post<PostUserStoryResponse>("/study/editstory", {
      ucsCode: ucsId,
      reviewMode,
      postStoryEdit: txtStory,
      postStoryPublic: isPublic,
    });
  }

  // Edit Custom Keyword dialog
  postUserKeyword(ucsId: number, keyword: string) {
    return this.post<PostUserKeywordResponse>("/study/editkeyword", {
      ucsId,
      keyword,
    });
  }

  // Shared Stories action : star / report / copy
  ajaxSharedStory(params: PostVoteStoryRequest) {
    return this.post<PostVoteStoryResponse>("/study/ajax", params);
  }

  //
  addCard(ucsId: TUcsId) {
    return this.post("/flashcards/add", {
      ucs: ucsId,
    });
  }

  // get vocab entries for the Dictionary (example words for given kanji)
  getDictListForUCS(ucsId: number, getKnownKanji: boolean) {
    return this.get<GetDictListForUCS>("/study/dict", {
      ucs: ucsId,
      reqKnownKanji: getKnownKanji,
    });
  }

  // return results for multiple kanji (Kanji Recognition, unused)
  getDictCacheFor(chars: string) {
    return this.get<GetDictCacheFor>("/study/dictcache", {
      chars: chars,
    });
  }

  setVocabForCard(ucs: number, dictid: number) {
    return this.post("/study/vocabpick", {
      ucs: ucs,
      dictid: dictid,
    });
  }

  deleteVocabForCard(ucs: number) {
    return this.post("/study/vocabdelete", { ucs });
  }
}

export function getApi(): LegacyApi {
  const apiBaseUrl = baseUrl();

  return LegacyApi.getInstance(apiBaseUrl);
}
