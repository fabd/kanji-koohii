/**
 * Create an absolutely positioned div that covers an area of the page
 * with a solid color and a level of transparency. Typical use is to show an
 * area as "disabled" while a dialog is on, or while content is loading with ajax.
 *
 * OPTIONS
 *
 *   target      {HTMLElement}  Element which is covered by the mask
 *
 */

import VueInstance, { ComponentPublicInstance } from "@lib/helpers/vue-instance";
import KoohiiLoading from "./KoohiiLoading.vue";
import { getStyle } from "@lib/koohii/dom";
import Lang from "@lib/core/lang";

type VueInstanceOf<T> = T extends new () => infer I ? I : never;
type ExtractComponentInstance<T> = T extends new ()=> infer I ? I : never

const setMaskStyle = (parent: HTMLElement, instance: ExtractComponentInstance<typeof KoohiiLoading>) => {
  instance.originalPosition = getStyle(parent, "position");
};

let instance: any = null;

export type KoohiiLoadingOptions = { target: HTMLElement; background?: string };

export default {
  /**
   *
   *   target     {HTMLElement}     target to cover with mask
   *   background {String}          background color of the mask
   *
   * @param {} options
   */
  show(options: KoohiiLoadingOptions) {
    console.log("koohiiloading::show()");
    let target = options.target;

    console.assert(Lang.isNode(target), "KoohiiLoading() : target is invalid");

    instance = VueInstance(KoohiiLoading, document.createElement("div"), options); // as TVueInstanceOf<typeof KoohiiLoading>;

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
