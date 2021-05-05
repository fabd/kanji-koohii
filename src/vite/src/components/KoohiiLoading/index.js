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

import Vue from "vue";
import Component from "./KoohiiLoading.vue";
import { getStyle } from "@lib/koohii/dom";
import Lang from "@lib/core/lang";

/*
interface KoohiiLoadingInstance extends Vue {
  visible: boolean;
  originalPosition: string | null;
  close(): void;
}*/

/**
 * @param {HTMLElement}    parent
 * @param {any} instance  Vue component instance
 */
const setMaskStyle = (parent, instance) => {
  instance.originalPosition = getStyle(parent, "position");
};

/** @type {any} KoohiiLoading component instance */
let inst = null;

export default {
  /**
   *
   *   target     {HTMLElement}     target to cover with mask
   *   background {String}          background color of the mask
   *
   * @param {{ target: HTMLElement; background?: string }} options
   */
  show(options) {
    console.log("koohiiloading::show()");
    let target = options.target;

    console.assert(Lang.isNode(target), "KoohiiLoading() : target is invalid");

    const LoadingConstructor = Vue.extend(Component);
    const instance = new LoadingConstructor({
      el: document.createElement("div"),
      propsData: options,
    });

    setMaskStyle(target, instance);

    if (
      instance.originalPosition !== "absolute" &&
      instance.originalPosition !== "fixed"
    ) {
      target.classList.add("kk-loading-target--relative");
    }

    target.appendChild(instance.$el);
    Vue.nextTick(() => {
      instance.visible = true;
    });

    inst = instance;
  },

  hide() {
    console.log("koohiiloading::hide()");
    inst && inst.close();
    inst = null;
  },
};
