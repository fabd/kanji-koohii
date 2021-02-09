import axios, { AxiosError, AxiosInstance, AxiosResponse } from "axios";
import {
  GetDictListForUCS,
  PostUserStoryResponse,
  PostVoteStoryRequest,
  PostVoteStoryResponse,
} from "@core/api/models";
import * as TRON from "@lib/koohii/tron";

const API_DEFAULT_TIMEOUT = 5000;

// In the future with a proper API we may have: stories, users, etc.
//  for now, `legacy` means the old ajax endpoints (not a standalone API)
export type KoohiiAPI = {
  legacy: LegacyApi;
};

abstract class HttpClient {
  protected readonly axiosInst: AxiosInstance;

  public constructor(baseURL: string) {
    this.axiosInst = axios.create({
      baseURL: baseURL,
      timeout: API_DEFAULT_TIMEOUT,
    });

    this._initResponseInterceptors();
  }

  private _initResponseInterceptors = () => {
    this.axiosInst.interceptors.response.use(
      // Any status code that lie within the range of 2xx cause this function to trigger
      this._responseThenInterceptor,
      // Any status codes that falls outside the range of 2xx cause this function to trigger
      this._responseCatchInterceptor
    );
  };

  private _responseThenInterceptor(response: AxiosResponse) {
    console.log("HttpClient then() interceptor %o", response);

    return response;
  }

  protected _responseCatchInterceptor(error: AxiosError) {
    // console.warn("HttpClient catch() - %o", error);

    let t = TRON.Inst<any>({ status: TRON.STATUS.FAILED });

    // The server responded with a status code that falls out of the range of 2xx
    if (error.response) {
      // response { data, status, headers }
      console.warn("HttpClient(response error): status code ", error.response.status);
      t.setErrors(`Oops! Server responded with error ${error.response.status}`);
    }
    // The request was made but no response was received
    //  `error.request` is an instance of XMLHttpRequest in the browser
    else if (error.request) {
      console.warn("HttpClient(request error): ", error);
      t.setErrors(`Oops! The request timed out.`);
    }
    // Something happened in setting up the request that triggered an Error
    else {
      console.warn("HttpClient(unknown error): ", error.message);
      t.setErrors("Request error");
    }

    return Promise.reject(error);
  }
}

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

  postUserStory(
    ucsId: number,
    txtStory: string,
    isPublic: boolean,
    reviewMode: boolean
  ) {
    return this.axiosInst
      .post<TRON.TronMessage>("/study/editstory", {
        ucsCode: ucsId,
        reviewMode,
        postStoryEdit: txtStory,
        postStoryPublic: isPublic,
      })
      .then((res) => TRON.Inst<PostUserStoryResponse>(res.data));
  }

  // Shared Stories action : star / report / copy
  ajaxSharedStory(params: PostVoteStoryRequest) {
    return this.axiosInst
      .post<TRON.TronMessage>("/study/ajax", params)
      .then((res) => TRON.Inst<PostVoteStoryResponse>(res.data));
  }

  // get vocab entries for the Dictionary (example words for given kanji)
  getDictListForUCS(ucsId: number, getKnownKanji: boolean) {
    return this.axiosInst
      .get<TRON.TronMessage>("/study/dict", {
        params: {
          ucs: ucsId,
          reqKnownKanji: getKnownKanji,
        },
      })
      .then((res) => TRON.Inst<GetDictListForUCS>(res.data));
  }

  setVocabForCard(ucs: number, dictid: number) {
    return this.axiosInst
      .post<TRON.TronMessage>("/study/vocabpick", {
        ucs: ucs,
        dictid: dictid,
      })
      .then((res) => TRON.Inst(res.data));
  }

  deleteVocabForCard(ucs: number) {
    return this.axiosInst
      .post<TRON.TronMessage>("/study/vocabdelete", { ucs })
      .then((res) => TRON.Inst(res.data));
  }
}

export function getApi(): KoohiiAPI {
  console.assert(!!window.KK_BASE_URL, "KK_BASE_URL is not set?");

  // base url to account for dev/test envs, no trailing slash
  const apiBaseUrl = window.KK_BASE_URL.replace(/\/$/, "");

  return {
    legacy: LegacyApi.getInstance(apiBaseUrl),
  };
}
