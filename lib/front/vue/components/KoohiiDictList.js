/**
 * Instantiate the Dictionary Lookup component (used on Study and FLashcard Review pages).
 *
 *
 * METHODS
 * 
 *   mount(<object> propsData, <element|selector> mountPoint)
 *
 *     ... Mount point element is replaced!
 * 
 *
 * BACKGROUND
 *
 *   We manually instantiate Vue components so we can hook into the legacy code. Plus
 *   it is more performant. This component is rendered by Vue only when the Dictionary
 *   is expanded in the Study pages, or the Dictionary dialog is opened during Flashcard
 *   Review.
 *
 *   We may also eventually use the smaller Vue build without the compiler, which in
 *   theory should work.
 * 
 */

import ComponentDefinition from './KoohiiDictList.vue'

export default {
   
  /**
   * Instantiate and mount the component to the DOM.
   * 
   * @param  {object}                 propsData
   * @param  {HTMLElement | string}   mountPoint
   * 
   */
  mount(propsData, mountPoint) {

    const vueConstructor = Vue.extend(ComponentDefinition);
    const vueInstance = new vueConstructor({ "propsData": propsData });

    vueInstance.$mount(mountPoint);
  }

}