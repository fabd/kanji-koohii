/**
 * STUDY bundle.
 *
 * Used in all /study pages.
 *
 */

// stylesheets
import "./assets/sass/study-base.build.css";

import { domGetById, domContentLoaded } from "@lib/dom";
import VueInstance from "@lib/helpers/vue-instance";
import StudyPage from "@app/study-page";
import MyStoriesTable from "@app/study/MyStoriesTable.vue";

domContentLoaded(() => {
  console.log("@entry-study");

  let elMount: HTMLElement;

  if ((elMount = domGetById("MyStoriesSelect")!)) {
    VueInstance(MyStoriesTable, elMount);
  }

  StudyPage.initialize();
});
