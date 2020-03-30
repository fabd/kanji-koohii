// bundle used on the Study pages
// requires the root bundle!

// dependency for `css-loader` which includes our legacy stylesheets in the webpack build!
import "@web/koohii/study-base.build.scss";
import "@web/koohii/manage.build.scss";

import LeitnerChart from "@components/LeitnerChart.vue";
import KoohiiDictList from "@components/KoohiiDictList.vue";
import KoohiiEditStory from "@components/KoohiiEditStory.vue";

// declare some globals to be available for legacy Javascript (non-Vue build)
import { KoohiiAPI } from "@lib/KoohiiAPI";
import { merge } from "@lib/koohii/lang";

if (window.Koohii) {
  // for legacy code upgrade path
  window.Koohii.API = KoohiiAPI;

  merge(window.Koohii.UX, {
    LeitnerChart,
    KoohiiDictList,
    KoohiiEditStory,
  });
} else {
  console.warn("Koohii global not defined.");
}

console.log("study bundle");
