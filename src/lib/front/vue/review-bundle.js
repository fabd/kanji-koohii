// FLASHCARD REVIEW bundle
import "@web/koohii/kanji-flashcardreview.build.scss";

import KoohiiDictList from "@components/KoohiiDictList.vue";
import KoohiiEditStory from "@components/KoohiiEditStory.vue";
import KoohiiFlashcard from "@components/KoohiiFlashcard.vue";

// declare some globals to be available for legacy Javascript (non-Vue build)
import { KoohiiAPI } from "@lib/KoohiiAPI";
import { merge } from "@lib/koohii/lang";

if (window.Koohii) {
  // for legacy code upgrade path
  window.Koohii.API = KoohiiAPI;

  merge(window.Koohii.UX, {
    KoohiiDictList, // dictionary list (Study & Flashcard Review)
    KoohiiFlashcard, // (wip) review page refactoring
    KoohiiEditStory, // Edit Story dialog
  });
} else {
  console.warn("Koohii.UX not defined.");
}

let x = merge(false);
