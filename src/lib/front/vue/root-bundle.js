/**
 * Common bundle, includes
 * 
 *   - Vue runtime
 *   - the main (legacy) stylesheet
 *   - Mobile navigation
 * 
 */

// Vue : bundle "standalone" build with the components
import Vue from 'vue'

// includes the main legacy stylesheet in the webpack build
import '@web/koohii/main.build.scss';

// turn off annoying messages 
Vue.config.productionTip = false
Vue.config.devtools = false;

// include site-wide mobile navigation in the root bundle (for the landing page)
import KoohiiAside     from '@components/Aside.js'

// make available to the Vue instance used in the web app's pages (non-SPA)
window.Vue = Vue

// export our DOM library for inline Javascript code
import KOOHII from '@lib/koohii/koohii.js'
let K = window.Koohii = KOOHII

// namespace for our front-end assets
K.UX = {
  KoohiiAside
}

// Refs are instances of components
K.Refs = { }

import VueInstance from '@lib/helpers/vue-instance.js'
window.VueInstance = VueInstance

// console.log('root bundle', );
