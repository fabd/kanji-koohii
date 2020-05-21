/**
 * Common dependencies for the webpack entry points.
 *
 *   - Vue runtime
 *   - Mobile navigation
 *
 */

// Vue : bundle "standalone" build with the components
import Vue from "vue";

// turn off annoying messages
Vue.config.productionTip = false;
Vue.config.devtools = false;

// include site-wide mobile navigation in the root bundle (for the landing page)
import KoohiiAside from "@components/Aside.js";

// export utilities to the legacy front end
import Dom from "@lib/koohii/dom";
import Lang from "@lib/koohii/lang";
import VueInstance from "@lib/helpers/vue-instance";

export function init() {
  const koohiiGlobals: KoohiiGlobals = {
    Dom: Dom,
    Refs: {},
    Util: {
      Lang: Lang,
    },
    UX: {},
  };

  // make available to the Vue instance used in the web app's pages (non-SPA)
  window.Vue = Vue as any; // fix wtf with "VueConstructor<Vue> & typeof Vue" errors
  window.VueInstance = VueInstance;
  window.Koohii = koohiiGlobals;

  // references for instancing components
  window.Koohii.UX = {
    KoohiiAside,
  };

  console.log("@root-bundle");
}
