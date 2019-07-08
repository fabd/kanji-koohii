/**
 *
 * F.A.Q.:
 * 
 *    new vueConstructor() options:
 *
 *      el                 https://vuejs.org/v2/api/#el
 *      data               ???
 *      propsData          https://vuejs.org/v2/api/#propsData
 *      
 */


import Lang from '@lib/koohii/lang.js'
const
  isNode      = Lang.isNode,
  isString    = Lang.isString


/**
 * Helper to instantiate a Vue component.
 * 
 * @param {Object} component
 * @param {String | HTMLElement} mountPoint
 * @param {Object} propsData
 * @param {Boolean} replace   True if you want to replace the mount point element, otherwise it will be appended as a child
 *
 * @return {Object} Vue instance
 */
export default function(component, mountPoint, propsData, replace)
{
  const props  = propsData || {}

  const vueConstructor = Vue.extend(component)
  const instance       = new vueConstructor({ "propsData": props })

  let el = isString(mountPoint) ? document.querySelectorAll(mountPoint)[0] : mountPoint 
  console.assert(isNode(el), "VueInstance() : mountPoint is invalid")

  if (true === replace) {
    // this does replace the mount point element!
    instance.$mount(el)
  }
  else {
    // this doesn't replace the mount point element
    instance.$mount()
    el.appendChild(instance.$el)
  }

  return instance
} 
