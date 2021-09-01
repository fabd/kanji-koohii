/**
 * Declare globals for legacy components (.js).
 *
 *   Koohii
 *   . API             ... expose axios based API for review & study bundles
 *   . Dom             ... expose DOM utilities
 *
 */

interface Window {
  // cf. kk_globals_put() on the php side
  KK: {
    // base URL for API requests (cf. layout.php & koohii_base_url() helper)
    BASE_URL: string;

    // site-wide mobile nav, setup data from php with the correct urls
    MBL_NAV_DATA: Dictionary;

    STUDY_SEARCH_URL: string;

    EDITSTORY_PROPS: Dictionary;

    REVIEW_OPTIONS: Dictionary;
    REVIEW_MODE: Dictionary;

    // study > My Stories
    MYSTORIES_SORT_ACTIVE: string;
    MYSTORIES_SORT_OPTIONS: Dictionary;

    ACCOUNT_SRS: Dictionary;

    // Kanji Recognition
    READING_KEYWORDS: Dictionary;
  };

  // 4th/5th edition keywords and kanji, import cf. _SideColumn.php
  //   web/revtk/study/keywords-rtk-0.js
  //   web/revtk/study/keywords-rtk-1.js
  kklist: string;
  kwlist: string[];

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
