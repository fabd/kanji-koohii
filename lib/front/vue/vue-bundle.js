/**
 * vue-bundle.js - the main vue bundle including vue (standalone) and common components used across the app
 *
 * 
 * VUE BUILD & INSTANCING COMPONENTS
 *
 *   Components should be designed in a way that they are *instanced* by Javascript into the page.
 *
 *   For example, the mobile nav is not generated unless user click/tap the hamburger icon, only then
 *   the mobile nav component is instanced and generated into the page.
 *
 *   Why?
 *   
 *   - Speed:          components are pre-compiled.
 *       
 *   - Optimization:   eventually, we will want to switch to the runtime-only build of Vue, to
 *                     further reduce page load. The run-time-only can not parse templates found
 *                     in the html pages.
 *
 *   - Future:         in the future, if we want to switch some parts of the site to a SPA
 *                     like functionality, this will be much easier.
 * 
 *   
 */

// Vue : bundle "standalone" build with the components
import Vue from 'vue'


// turn off annoying messages 
Vue.config.productionTip = false
Vue.config.devtools = false;

// include site-wide mobile navigation in the root bundle (for the landing page)
import KoohiiAside     from 'components/Aside.js'

// make available to the Vue instance used in the web app's pages (non-SPA)
window.Vue = Vue

// export our DOM library for inline Javascript code
import KOOHII from 'lib/koohii/koohii.js'
let K = window.Koohii = KOOHII

// namespace for our front-end assets
K.UX = {
  KoohiiAside
}

// Refs are instances of components
K.Refs = { }

import VueInstance from 'lib/helpers/vue-instance.js'
window.VueInstance = VueInstance
