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

import { nextTick } from "vue";
import VueInstance from "@/lib/helpers/vue-instance";
import KoohiiLoading from "./KoohiiLoading.vue";
import { getStyle } from "@/lib/dom";
import Lang from "@/lib/lang";

let component: TVueInstanceOf<typeof KoohiiLoading> | null;
let componentUnmount: () => void;
let target: HTMLElement | null;

export default {
  /**
   *
   *   target     {HTMLElement}     target to cover with mask
   *   background {String}          background color of the mask
   *
   * @param {} props
   */
  show(props: { target: HTMLElement; background?: string }) {
    console.log("koohiiloading::show()");
    target = props.target;

    console.assert(Lang.isNode(target), "KoohiiLoading() : target is invalid");

    const { vm, unmount } = VueInstance(KoohiiLoading, document.createElement("div"), props);
    component = vm;
    componentUnmount = unmount;

    component.originalPosition = getStyle(target, "position")!;

    target.classList.add("ko-loading-target--relative");  

    target.appendChild(component.$el);
    nextTick(() => {
      component!.setVisible(true);
    });
  },

  hide() {
    console.log("koohiiloading::hide()");
    if (component) {
      target!.classList.remove("ko-loading-target--relative");

      componentUnmount();
      component = null;
    }
  },
};
