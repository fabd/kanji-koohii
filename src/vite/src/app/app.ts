/**
 * OBSOLETE. Needs to be phased out eventually.
 */

import $$ from "@lib/dom";
import VueInstance from "@lib/helpers/vue-instance";
import AjaxTable from "@old/ajaxtable";
import EventDelegator from "@old/eventdelegator";

const App = {
  // helper to instance a Vue from php templates
  VueInstance,

  // components instanced from php templates
  Ui: {
    AjaxTable,
  },

  bodyED: null as IEventDelegator | null,

  /** @type String  selector for input to focus after DOMContentLoaded */
  focusOnLoadSel: "",

  /**
   * Constructor.
   *
   */
  init() {
    console.log("App.init()");

    // focus input on load
    if (this.focusOnLoadSel) {
      const elFocus = $$(this.focusOnLoadSel)[0] as HTMLElement;
      elFocus && elFocus.focus();
    }
  },

  /**
   * Pass a selector for an element (typically an input field) to focus.
   * 
   * @param {String} selector  selector to pick element to focus
   */
  focusOnLoad(selector: string) {
    this.focusOnLoadSel = selector;
  },

  /**
   * Returns an EventDelegator instance for click events on the page body.
   *
   * @return  {Object}   EventDelegator instance.
   */
  getBodyED() {
    return this.bodyED
      ? this.bodyED
      : (this.bodyED = new (EventDelegator as IEventDelegator)(document.body, "click"));
  },
};

export default App;
