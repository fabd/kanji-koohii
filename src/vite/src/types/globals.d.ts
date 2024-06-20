/**
 * Types for globals received from php, passed into Window.KK.
 *
 * Also types for legacy .js components.
 *
 */

// legacy class
type AjaxPanelOpts = {
  form?: string | false;
  events?: {
    onResponse?: any;
    onFailure?: any;
    onContentInit?: any;
    onContentDestroy?: any;
    onSubmitForm?: any;
  };
  bUseLayer?: boolean;
  bUseShading?: boolean;
  initContent?: boolean;
  [key: string]: any;
};

// legacy class
type AjaxDialogOpts = {
  requestUri: string;
  requestData?: Dictionary<any>;
  skin?: string;
  mobile?: boolean;
  close: boolean;
  width: number;
  scope: any;
  events: Dictionary<any>;
  context?: [string, string, string, null, any];
};

// --------------------------------------------------------------------
// 
// --------------------------------------------------------------------

// lessons data as formatted in the keywords file, cf. generating script kanjis_table.php
// - the lessons array is formatted as tuples, to be loaded into a new Map()
// - the Map's key is the lesson number, starting at 1
type TSeqLessonsMeta = { sequenceName: string; lessons: TSeqLessonEntry[] };
type TSeqLessonId = number;
type TSeqLessonEntry = [key: TSeqLessonId, value: TSeqLessonData];
type TSeqLessonData = [from: number, count: number];
type TSeqLessonMap = Map<TSeqLessonId, TSeqLessonData>;

interface Window {
  // cf. kk_globals_put() on the php side
  KK: {
    // base URL for API requests, is *always* set
    BASE_URL: string;

    // the new homepage dashboard (03/2022)
    HOMEDASH_PCTBAR_PROPS: Dictionary;
    HOMEDASH_LESSON_PROPS: Dictionary;

    // the Leitner Chart (Spaced Repetition homepage)
    LEITNER_CHART_DATA: TLeitnerChartData;

    // custom review page
    CUSTOM_REVIEW_PROPS: Dictionary;

    // site-wide mobile nav, setup data from php with the correct urls
    MBL_NAV_DATA: Dictionary;

    STUDY_SEARCH_URL: string;

    // sets current page id for the Last Viewed component on Study page
    LASTVIEWED_UCS_ID: number;

    EDITSTORY_PROPS: Dictionary;

    REVIEW_OPTIONS: { fcrOptions: TReviewOptions; props: Dictionary };
    REVIEW_MODE: Dictionary;

    // study > My Stories
    MYSTORIES_SORT_ACTIVE: string;
    MYSTORIES_SORT_OPTIONS: Dictionary;

    ACCOUNT_SRS: Dictionary;

    // Old/New edition RTK keywords and kanji
    SEQ_KANJIS: string;
    SEQ_KEYWORDS: string[];
    SEQ_LESSONS: TSeqLessonsMeta;

    // User data (someday/maybe we may have a global user state on the JS side)
    USER_KEYWORDS_MAP: Dictionary;
    USER_KANJI_CARDS: TUserKanjiCard[];
  };

  Koohii: {
    // misc. references shared between backend/frontend,
    //  also Vue components from Vite build, instanced from php templates
    Refs: {
      [key: string]: any;
    };

    // references to Vue components that can be instanced later
    UX: { [componentName: string]: any };
  };
}
