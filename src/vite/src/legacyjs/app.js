/**
 * OBSOLETE. Needs to be phased out eventually.
 */

import $$ from "@lib/koohii/dom.ts";
import Core from "@old/core";
import EventDelegator from "@old/ui/eventdelegator";

let App = {};

import AjaxTable from "@old/ui/ajaxtable";
App.Ui = {
  AjaxTable,
};

App = Object.assign(App, {
  /**
   * EventDelegator.
   */
  bodyED: null,

  /** @type String  selector for input to focus after DOMContentLoaded */
  focusOnLoadSel: "",

  /**
   * Constructor.
   *
   */
  init: function () {
    console.log("App.init()");

    // initialize the page
    if (this.fnReady) {
      console.log("App.ready()");
      this.fnReady();
    }

    // focus input on load (AFTER fnReady())
    if (this.focusOnLoadSel) {
      const elFocus = $$(this.focusOnLoadSel)[0];
      elFocus && elFocus.focus();
    }
  },

  /**
   * Pass a selector for an element (typically an input field) to focus.
   * @param {String} selector  selector to pick element to focus
   */
  focusOnLoad: function (selector) {
    this.focusOnLoadSel = selector;
  },

  ready: function (fn) {
    this.fnReady = fn;
  },

  /**
   * Returns an EventDelegator instance for click events on the page body.
   *
   * @return  {Object}   EventDelegator instance.
   */
  getBodyED: function () {
    return this.bodyED
      ? this.bodyED
      : (this.bodyED = new EventDelegator(document.body, "click"));
  },
});

window.addEventListener("DOMContentLoaded", () => {
  App.init();
});

export default App;
