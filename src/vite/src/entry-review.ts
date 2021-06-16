/**
 * Fullscreen Flashcard Review BUNDLE.
 */

// import legacy stylesheets
import "@css/kanji-review.build.scss";

// components instanced by external code
import KoohiiDictList from "@/vue/KoohiiDictList.vue";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";
window.Koohii.UX = {
  ...window.Koohii.UX,
  KoohiiDictList, // dictionary list (Study & Flashcard Review)
  KoohiiEditStory, // Edit Story dialog
};

import { domContentLoaded } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import Review from "@app/review/review-kanji";

domContentLoaded(() => {
  console.log("@entry review ...");
  new Review(kk_globals_get("REVIEW_OPTIONS"));
});
