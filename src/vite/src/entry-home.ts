import VueInstance from "@lib/helpers/vue-instance";
import $$, { domContentLoaded } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";

import KoPctBar from "@/vue/KoPctBar.vue";

class HomeDashboard {
  constructor() {
    const elMountPctBar = $$("#JsDashboardPctBar")[0];
    if (elMountPctBar) {
      const props = kk_globals_get("HOMEDASH_PCTBAR_PROPS");
      VueInstance(KoPctBar, elMountPctBar, props);
    }
  }
}

domContentLoaded(() => {
  console.log("@entry-home");
  new HomeDashboard();
});
