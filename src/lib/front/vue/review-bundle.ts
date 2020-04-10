/**
 * LANDING PAGE BUNDLE
 *
 *   Includes:
 *   - root-bundle: Vue, mobile navigation, globals
 *   - the landing page stylesheet
 *   - components instanced externally by Flashcard Review page
 *
 */

// import legacy stylesheets
import "@web/koohii/main.build.scss";
import "@web/koohii/kanji-flashcardreview.build.scss";

import * as RootBundle from "@lib/helpers/root-bundle";
RootBundle.init();

// components instanced by external code
import KoohiiDictList from "@components/KoohiiDictList.vue";
import KoohiiEditStory from "@components/KoohiiEditStory.vue";
import KoohiiFlashcard from "@components/KoohiiFlashcard.vue";

// declare some globals to be available for legacy Javascript (non-Vue build)
import { KoohiiAPI } from "@lib/KoohiiAPI";

// API used by legacy front end
window.Koohii.API = KoohiiAPI;

window.Koohii.UX = {
  ...window.Koohii.UX,
  KoohiiDictList, // dictionary list (Study & Flashcard Review)
  KoohiiFlashcard, // (wip) review page refactoring
  KoohiiEditStory, // Edit Story dialog
};

console.log("@review-bundle");
