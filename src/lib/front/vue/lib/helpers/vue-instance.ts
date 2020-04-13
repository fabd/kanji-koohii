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

import Lang from "@lib/koohii/lang";
import Vue from "vue";
import { VueConstructor } from "vue/types/umd";
// import { PropOptions, PropsDefinition } from "vue/types/options";

export interface VueInstanceFn {
  (
    component: VueConstructor<Vue>,
    mount: Element,
    props: { [key: string]: any },
    replace?: boolean
  ): Vue;
}

/**
 * Helper to instantiate a Vue component.
 *
 * @param props     (optional) component propsData
 * @param replace   true to replace the mount element, false to append as a child
 * @returns Vue instance
 */
const VueInstance: VueInstanceFn = function(
  component,
  mount,
  props = {},
  replace = false
): Vue {
  const vueConstructor = Vue.extend(component);
  const instance = new vueConstructor({ propsData: props });

  let el = Lang.isString(mount) ? document.querySelectorAll(mount)[0] : mount;
  console.assert(Lang.isNode(el), "VueInstance() : mount is invalid");

  if (true === replace) {
    // this does replace the mount point element!
    instance.$mount(el);
  } else {
    // this doesn't replace the mount point element
    instance.$mount();
    el.appendChild(instance.$el);
  }

  return instance;
};

export default VueInstance;
