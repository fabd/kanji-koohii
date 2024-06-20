import VueInstance from "@lib/helpers/vue-instance";
import $$, { domContentLoaded } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import * as RTK from "@/lib/rtk";

import KoLessonPane from "@/vue/KoLessonPane.vue";
import KoPctBar from "@/vue/KoPctBar.vue";
import KoLessonsChart from "@/vue/KoLessonsChart.vue";

class HomeDashboard {
  constructor() {
    let elMount;

    elMount = $$("#JsHomePctBar")[0];
    if (elMount) {
      VueInstance(KoPctBar, elMount, kk_globals_get("HOMEDASH_PCTBAR_PROPS"));
    }

    elMount = $$("#JsHomeLesson")[0];
    if (elMount) {
      VueInstance(
        KoLessonPane,
        elMount,
        kk_globals_get("HOMEDASH_LESSON_PROPS")
      );
    }

    elMount = $$("#JsViewAllLessons")[0];
    if (elMount) {
      VueInstance(
        KoLessonsChart,
        elMount,
        {
          sequenceName: RTK.getSeqName(),
          lessons: RTK.getSeqLessons(),
        }
      );
    }
  }
}

domContentLoaded(() => {
  console.log("@entry-home");
  new HomeDashboard();
});
