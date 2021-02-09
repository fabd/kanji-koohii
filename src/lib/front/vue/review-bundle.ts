/**
 * Fullscreen Flashcard Review BUNDLE.
 */

// import legacy stylesheets
import "@web/koohii/main.build.scss";
import "@web/koohii/kanji-flashcardreview.build.scss";

import * as RootBundle from "@lib/helpers/root-bundle";
RootBundle.init();

// make the axios based API (vue build) available to legacy js
import { getApi } from "@core/api";
window.Koohii.API = window.Vue.prototype.$api = getApi();

// components instanced by external code
import KoohiiDictList from "@components/KoohiiDictList.vue";
import KoohiiEditStory from "@components/KoohiiEditStory.vue";
import KoohiiFlashcard from "@components/KoohiiFlashcard.vue";

// declare some globals to be available for legacy Javascript (non-Vue build)
window.Koohii.UX = {
  ...window.Koohii.UX,
  KoohiiDictList, // dictionary list (Study & Flashcard Review)
  KoohiiFlashcard, // (wip) review page refactoring
  KoohiiEditStory, // Edit Story dialog
};

console.log("@review-bundle");
