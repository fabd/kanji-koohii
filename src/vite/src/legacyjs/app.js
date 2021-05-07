/**
 * Creates a basic framework and namespace for the client-side code of the
 * application.
 *
 * App
 *   Application namespace, initializes the page contents.
 *
 * App.env
 *   Environment for client side, mostly used to pass data to javascript.
 *   - App.env is defined here and can be extended by <script> tags in the page
 *
 * App.Helper
 *
 * App.getBodyED()
 * App.ready(fn)
 * 
 */

// legacy YUI2 is loaded separately by <script>
if (!window.YAHOO) {
  console.warn('YAHOO is not loaded');
}


// namespace
var App = window.App = {};

/**
 * Global application configuration.
 *
 * Options:
 *
 * These options MUST be set via backend templates, AFTER app.js (this) is
 * included:
 *
 *  App.env.logging (boolean)
 *    Enable javascript console in, set false for production. If possible all
 *    App.log() calls should be stripped from the production javascript.
 *
 */
App.env = {};

/**
 * Application specific UI helpers go here.
 *
 *   generateDialogMarkup()      Generate markup for a dialog, ready to use with Core.Ui.AjaxDialog
 */
App.Ui = {};

/**
 * Global application object
 *
 */

var Y = YAHOO,
  $$ = Koohii.Dom,
  Dom = Y.util.Dom,
  Event = Y.util.Event;

Y.lang.augmentObject(App, {
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
   * @return  {Object}   Core.Ui.EventDelegator instance.
   */
  getBodyED: function () {
    return this.bodyED
      ? this.bodyED
      : (this.bodyED = new Core.Ui.EventDelegator(document.body, "click"));
  },
});

Core.ready(function () {
  App.init();
});

export default App;
