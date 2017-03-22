import Component from './Aside.vue'

import KoohiiNavMenu from './KoohiiNavMenu.js'

let Constructor = null
let instance    = null

export default {
  
  // options
  //   navOptions
  open(options) {
    // console.log("Aside open()")

    if (!instance) {

      if (!Constructor) {
        Constructor = Vue.extend(Component)
      }

      // create instance in “unmounted” state, without an associated DOM element
      instance = new Constructor({
        data: {
          // merged with defaults in the component
          width: 280,
        }
      })

      // render off-document and append afterwards
      instance.$mount()
      document.body.appendChild(instance.$el)

      // render nav
      KoohiiNavMenu.mount({
        el:    instance.$refs.navContent,
        menu:  options.navOptionsMenu
      })
    }

    instance.show = true

    Vue.nextTick(() => {
      KoohiiNavMenu.initit()
    })
  },

  close() {
    // console.log("Aside close()")

    instance.show = false
  },

  toggle() {
    if (!instance || !instance.show) {
      this.open();
    }
    else {
      this.close();
    }
  }

}