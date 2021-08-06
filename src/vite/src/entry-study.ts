/**
 * This bundle is used on all pages, except landing (home) & flashcard review.
 */

// stylesheets
import "@css/main.build.scss";
import "@css/study-base.build.scss";

import rootBundleInit from "@app/root-bundle";
rootBundleInit();

import { domGetById, domContentLoaded } from "@lib/dom";
import VueInstance from "@lib/helpers/vue-instance";
import StudyPage from "@app/study-page";
import MyStoriesTable from "@app/study/MyStoriesTable.vue";

domContentLoaded(() => {
  console.log("@entry-study");

  // My Stories page?
  let elMount = domGetById('MyStoriesSelect')!;
  if (elMount) {
    VueInstance(MyStoriesTable, elMount);
  }

  StudyPage.initialize();

  // console.log("@entry study ...done");
});
