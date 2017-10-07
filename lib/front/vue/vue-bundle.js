// vue-bundle.js - the main vue bundle including vue (standalone) and common components used across the app
/**
 * IMPORTANT
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
 * NOTES
 *
 *   For vue-loader
 *
 *   $ npm install vue@2.1.6 --save
 *   
 */

// Vue : bundle "standalone" build with the components
import Vue from 'vue'

// turn off annoying messages 
Vue.config.productionTip = false
Vue.config.devtools = false;

// 
import CoreJS from './lib/coreJS.js'

// Mobile Navigation
import KoohiiAside     from './components/Aside.js'
import KoohiiNavMenu   from './components/KoohiiNavMenu.js'

// Koohii
import LeitnerChart    from './components/LeitnerChart.vue'
import KoohiiDictList  from './components/KoohiiDictList.js'
import KoohiiFlashcard from './components/KoohiiFlashcard.vue'

// make available to the Vue instance used in the web app's pages (non-SPA)
window.Vue = Vue

// export our DOM library for inline Javascript code
window.CoreJS = CoreJS

window.Koohii = {
  UX: {
    KoohiiAside,
    KoohiiNavMenu,
    LeitnerChart,
    KoohiiDictList,         // dictionary list (Study & Flashcard Review)
    KoohiiFlashcard         // (wip) review page refactoring
  }
}
