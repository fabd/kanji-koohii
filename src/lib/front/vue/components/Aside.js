import Component     from './Aside.vue'
import KoohiiNavMenu from './KoohiiNavMenu.vue'
import VueInstance   from '@lib/helpers/vue-instance.js'

let instance   = null
let navMenu    = null

export default {
  
  // options.navOptionsmenu (cf. apps/koohii/templates/layout.php)
  open(options) {
    // console.log("Aside open()")

    if (!instance) {
      // merged with defaults in the component
      let data = { width: 280 }
      // render off-document and append afterwards
      instance = VueInstance(Component, document.body, data, /* do NOT replace mount point */ false)

      // render nav
      navMenu = VueInstance(KoohiiNavMenu, instance.$refs.navContent, { menu: options.navOptionsMenu })
    }

    instance.show = true

    Vue.nextTick(() => {
      navMenu.initCollapsedItems()
    })
  },

  close() {
    // console.log("Aside close()")

    instance.show = false
  },

  toggle() {
    if (!instance || !instance.show) {
      this.open();
    } else {
      this.close();
    }
  },
};
