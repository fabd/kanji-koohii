/**
 * Common bundle, includes
 *
 *   - Vue runtime
 *   - the main (legacy) stylesheet
 *   - Mobile navigation
 *
 */

// Vue : bundle "standalone" build with the components
import Vue from "vue";

// includes the main legacy stylesheet in the webpack build
import "@web/koohii/main.build.scss";

// turn off annoying messages
Vue.config.productionTip = false;
Vue.config.devtools = false;

// include site-wide mobile navigation in the root bundle (for the landing page)
import KoohiiAside from "@components/Aside.js";

// export utilities for legacy code
import Dom from "@lib/koohii/dom";
import Lang from "@lib/koohii/lang";
import VueInstance from "@lib/helpers/vue-instance";

/** @type {import("@lib/koohii/globals").KoohiiGlobals} */
const koohiiGlobals = {
  Dom: Dom,
  Refs: {},
  Util: {
    Lang: Lang,
  },
  UX: {},
};

// make available to the Vue instance used in the web app's pages (non-SPA)
window.Vue = Vue;
window.VueInstance = VueInstance;
window.Koohii = koohiiGlobals;

// references for instancing components
window.Koohii.UX = {
  KoohiiAside,
};

// console.log('root bundle', );
