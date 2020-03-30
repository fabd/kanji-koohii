/**
 * LANDING PAGE bundle, includes
 *
 *   - Vue runtime
 *   - the LANDING PAGE stylesheet
 *   - Mobile navigation
 *
 */

// Vue : bundle "standalone" build with the components
import Vue from "vue";

// includes the main legacy stylesheet in the webpack build
import "@web/koohii/home.build.scss";

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

window.Vue = Vue;
window.VueInstance = VueInstance;
window.Koohii = koohiiGlobals;

// namespace for our front-end assets
window.Koohii.UX = {
  KoohiiAside,
};

// console.log('landing page bundle', );
