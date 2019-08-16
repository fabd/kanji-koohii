/**
 * Create an absolutely positioned div that covers an area of the page
 * with a solid color and a level of transparency. Typical use is to show an
 * area as "disabled" while a dialog is on, or while content is loading with ajax.
 * 
 * 
 * OPTIONS
 *
 *   target      {HTMLElement}  Element which is covered by the mask
 *
 * 
 * METHODS
 * 
 *   show()
 *   hide()
 *
 * 
 * TODO
 *
 *   - always destroy the mask after 10 seconds or so, in case of timeout so the user can re-submit a form?
 * 
 */

import KoohiiLoading from './KoohiiLoading.vue'

import { classList, getStyle } from '@lib/koohii/dom.js'

import Lang, { merge } from '@lib/koohii/lang.js'
const isNode = Lang.isNode

const setMaskStyle = (options, parent, instance) => {
  // let maskStyle = {}

  instance.originalPosition = getStyle(parent, 'position')

  // Object.keys(maskStyle).forEach(property => {
  //   instance.$el.style[property] = maskStyle[property];
  // })
}


export default {

  data: function() {
    return {
      koohiiloadingInst: null
    }
  },

  methods: {
    /**
     *
     *   target     {HTMLElement}     target to cover with mask
     *
     *   background {String}          background color of the mask
     * 
     */
    koohiiloadingShow(options)
    {
      // console.log('koohiiloadingShow()')
      let target = options.target

      console.assert(isNode(target), "KoohiiLoading() : target is invalid")

      options = merge({}, /*defaults,*/ options);

      const LoadingConstructor = Vue.extend(KoohiiLoading)
      const instance = new LoadingConstructor({
        el: document.createElement('div'),
        data: options
      })

      setMaskStyle(options, target, instance)

      if (instance.originalPosition !== 'absolute' && instance.originalPosition !== 'fixed') {
        classList.add(target, 'kk-loading-target--relative')
      }

      target.appendChild(instance.$el)
      Vue.nextTick(() => {
        instance.visible = true
      })

      this.koohiiloadingInst = instance
    },

    koohiiloadingHide()
    {
      // console.log('koohiiloadingHide()')
      this.koohiiloadingInst.close()
      this.koohiiloadingInst = null
    }
  }

}
