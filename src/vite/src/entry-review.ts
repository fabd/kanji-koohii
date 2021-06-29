/**
 * Review bundle:
 *
 *   - SRS home with the Leitner chart
 *   - fullscreen flashcard review
 */

// import legacy stylesheets
import "@css/kanji-review.build.scss";

import { domGetById, domContentLoaded } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import VueInstance from "@lib/helpers/vue-instance";
import KanjiReview from "@app/review/review-kanji";
import LeitnerChart from "@/vue/LeitnerChart.vue";

domContentLoaded(() => {
  console.log("@entry review ...");

  let elRoot;

  // Leitner chart page
  if ((elRoot = domGetById("leitner-chart_pane"))) {
    VueInstance(LeitnerChart, elRoot, { containerId: "leitner-chart_pane" });
  }

  // review page
  if (domGetById("uiFcMain")) {
    window.Koohii.Refs.KanjiReview = new KanjiReview(kk_globals_get("REVIEW_OPTIONS"));
  }
});
