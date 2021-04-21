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
 *
 * @author  Fabrice Denis
 */
/*global YAHOO, Core */

/* =require "/core/core.js" */
/* =require "/ui/ui.js" */

console.assert(
  typeof YAHOO !== "undefined" && YAHOO,
  "YAHOO not declared (requires YUI)"
);

// namespace
var App = {};

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
 * The filter extends Core.Ui.AjaxRequest (and thus, AjaxPanel and AjaxDialog)
 * with global handling of the success and failure responses.
 *
 * Because of unsolved YUI 302 Redirect headers missing, the backend returns a
 * HTTP 500 error along with custom headers with url redirect.
 *
 * Events:
 *
 *
 *
 * @see Core.Ui.AjaxPanel
 */
(function() {
  /**
   * rtkAjaxException server side generates a HTTP 500 with header 'RTK-Error'.
   */
  var HEADER_RTK_ERROR = "RTK-Error";

  App.AjaxFilter = {
    /**
     * Do some global pre-processing of a HTTP 2xx ajax response.
     *
     * @param  {Object}  o   The YAHOO.util.Connect response object.
     *
     * @return {Boolean}  Return true to proceed (eg. html replace), false to ignore the response.
     */
    onSuccess: function(o) {
      // show debug info from php code, the debug div exists only in dev environment
      var Dom = YAHOO.util.Dom,
        elDebug = Dom.get("AppAjaxFilterDebug");
      if (elDebug) {
        if (/^[^{]/.test(o.responseText)) {
          elDebug.innerHTML = "<pre>\n" + o.responseText + "\n</pre>";
          elDebug.style.display = "block";
        } else {
          elDebug.style.display = "none";
        }
      }

      if (o.status === 404) {
        var message =
          o.getResponseHeader[HEADER_RTK_ERROR] ||
          '("RTK-Error" header not set)';

        console.warn('App.AjaxFilter() Caught a HTTP 404: "%s"', message);

        return false;
      }

      return true;
    },

    /**
     * Do some global pre-processing of a HTTP status 400 or greater ajax response.
     *
     * @see    http://developer.yahoo.com/yui/connection/#failure
     *
     * @param  {Object}  o   The YAHOO.util.Connect response object.
     * @return {Boolean}  Return true to proceed (eg. use default error message display), false to ignore the response.
     */
    onFailure: function(o) {
      console.log("AjaxFilter.onFailure(%o)", o);

      if (o.status === 500) {
        var message =
          o.getResponseHeader[HEADER_RTK_ERROR] ||
          '("RTK-Error" header not set)';

        console.warn('App.AjaxFilter() Caught a HTTP 500 error: "%s"', message);

        return false;
      }

      // let default failure handling of AjaxRequest etc (eg. show the error message in AjaxIndicator)
      return true;
    },
  };
})();

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
(function() {
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
    focusOnLoadSel: '',

    /**
     * Constructor.
     *
     */
    init: function() {
      console.log("App.init()");

      // global response filter
      if (Core.Ui && Core.Ui.AjaxRequest) {
        Core.Ui.AjaxRequest.responseFilter = App.AjaxFilter;
      }

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
    focusOnLoad: function(selector) {
      this.focusOnLoadSel = selector;
    },

    ready: function(fn) {
      this.fnReady = fn;
    },

    /**
     * Returns an EventDelegator instance for click events on the page body.
     *
     * @return  {Object}   Core.Ui.EventDelegator instance.
     */
    getBodyED: function() {
      return this.bodyED
        ? this.bodyED
        : (this.bodyED = new Core.Ui.EventDelegator(document.body, "click"));
    },

    /**
     * Throws an alert box, disable in production!
     */
    alert: function(s) {
      alert(s);
    },
  });

  Core.ready(function() {
    App.init();
  });
})();
