/**
 * Review bundle:
 *
 *   - SRS home with the Leitner chart
 *   - Kanji & Vocab review modes
 */

// import legacy stylesheets
import "@css/kanji-review.build.scss";

import { domGetById, domContentLoaded } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import VueInstance from "@lib/helpers/vue-instance";
import LeitnerChart from "@/vue/LeitnerChart.vue";
import KanjiReview from "@app/review/review-kanji";
import VocabReview from "@app/review/review-vocab";

domContentLoaded(() => {
  console.log("@entry-review");

  let elRoot;

  // Leitner chart page
  if ((elRoot = domGetById("leitner-chart_pane"))) {
    VueInstance(LeitnerChart, elRoot, { containerId: "leitner-chart_pane" });
  }

  // Review page
  if (domGetById("uiFcMain")) {

    const reviewMode = kk_globals_get("REVIEW_MODE");
    const reviewOptions = kk_globals_get("REVIEW_OPTIONS");

    // initialize the correct review mode based on existing `fc_view` option
    if (reviewMode.fc_view === "kanji") {
      window.Koohii.Refs.KanjiReview = new KanjiReview(reviewOptions);
    } else if (reviewMode.fc_view === "vocabshuffle") {
      new VocabReview(reviewOptions);
    }
  }
});
