/**
 *
 * OPTIONS
 *
 *   el     HtmlElement|String   Mount point (element or selector)
 *   menu   Object
 *
 * menu:
 *   // array of menuitems
 *   items: [
 *     { label:  , id:  , icon: "fa-book", children: <array of child menuitems> }
 *     ...
 *   ],
 *   // optional default top level menu to be opened
 *   opened: "id"
 *
 *
 * TODO
 * - use scrollHeight & overflow "hidden" to solve offsetHeight headaches?
 *
 *
 * COMPATIBILITY
 *   This version (commit) works on:
 *   
 *     Samsung Galaxy Note 3 (SM-N9005)
 *     Galaxy Note 10.1 (GT-N8010)
 *     Meizu M3 Note
 *     Moto G2
 * 
 */

import ComponentDefinition from './KoohiiNavMenu.vue'

const ComponentName = 'KoohiiNavMenu'

let ComponentConstructor = null
let instance    = null

export default {
  
  mount(options) {
    // console.log(`${ComponentName}::mount(%o)`, options)

    // singleton
    if (!instance) {
      ComponentConstructor = Vue.extend(ComponentDefinition)

      instance = new ComponentConstructor({
        propsData: {
          menu: options.menu
        }
      })

      // render off-document and append afterwards:
      let el = typeof options.el === 'string' ? $(options.el)[0] : options.el || console.log('mount point is invalid')

      // this doesn't replace the container, unlike `instance.$mount(el)`
      instance.$mount()
      el.appendChild(instance.$el)
    }
  },

  initit() {
    instance.initCollapsedItems()
  },

  isMounted() {
    return instance !== null
  }
}