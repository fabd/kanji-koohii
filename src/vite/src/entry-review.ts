/**
 * Review bundle:
 *
 *   - SRS home with the Leitner chart
 *   - Kanji & Vocab flashcard review
 *   - Custom Review form
 */

// import legacy stylesheets
import "./assets/sass/kanji-review.build.scss";

import $$, { domContentLoaded } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import VueInstance from "@lib/helpers/vue-instance";

import LeitnerChart from "@/vue/LeitnerChart.vue";
import KanjiReview from "@app/review/review-kanji";
import VocabReview from "@app/review/review-vocab";
import CustomReviewFromJapText from "@/vue/review/CustomReviewFromJapText.vue";

domContentLoaded(() => {
  console.log("@entry-review");

  let elMount;

  // Leitner chart page
  elMount = $$("#JsLeitnerChartComponent")[0];
  if (elMount) {
    VueInstance(LeitnerChart, elMount, {
      containerId: "JsLeitnerChartComponent",
      chartData: kk_globals_get("LEITNER_CHART_DATA")
    });
  }

  // Custom Review form
  elMount = $$("#CustomReviewFromJapText")[0];
  if (elMount) {
    VueInstance(CustomReviewFromJapText, elMount, kk_globals_get("CUSTOM_REVIEW_PROPS"));
  }

  // Review page
  elMount = $$("#uiFcMain")[0];
  if (elMount) {
    const reviewMode = kk_globals_get("REVIEW_MODE");
    const { fcrOptions, props } = kk_globals_get("REVIEW_OPTIONS");

    // initialize the correct review mode based on existing `fc_view` option
    if (reviewMode.fc_view === "kanji") {
      window.Koohii.Refs.KanjiReview = new KanjiReview(fcrOptions, props);
    } else if (reviewMode.fc_view === "vocabshuffle") {
      new VocabReview(fcrOptions, props);
    }
  }
});
