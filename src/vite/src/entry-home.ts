import VueInstance from "@lib/helpers/vue-instance";
import $$, { domContentLoaded } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";

import KoLessonPane from "@/vue/KoLessonPane.vue";
import KoPctBar from "@/vue/KoPctBar.vue";

class HomeDashboard {
  constructor() {
    let elMount;

    elMount = $$("#JsHomePctBar")[0];
    if (elMount) {
      const props = kk_globals_get("HOMEDASH_PCTBAR_PROPS");
      VueInstance(KoPctBar, elMount, props);
    }

    elMount = $$("#JsHomeLesson")[0];
    if (elMount) {
      const props = kk_globals_get("HOMEDASH_LESSON_PROPS");
      VueInstance(KoLessonPane, elMount, props);
    }
  }
}

domContentLoaded(() => {
  console.log("@entry-home");
  new HomeDashboard();
});
