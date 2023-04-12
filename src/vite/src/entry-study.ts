/**
 * STUDY bundle.
 *
 * Used in all /study pages.
 *
 */

// stylesheets
import "./assets/sass/study-base.build.scss";

import { domGetById, domContentLoaded } from "@lib/dom";
import VueInstance from "@lib/helpers/vue-instance";
import StudyPage from "@app/study-page";
import MyStoriesTable from "@app/study/MyStoriesTable.vue";

domContentLoaded(() => {
  console.log("@entry-study");

  let elMount: HTMLElement;

  elMount = domGetById("MyStoriesSelect")!;
  elMount && VueInstance(MyStoriesTable, elMount);

  StudyPage.initialize();
});
