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
 * App.warn(message)
 * App.halt(message)
 * App.ready(fn)
 *
 *
 * @author  Fabrice Denis
 */
/*global YAHOO, alert, console, document, window, Core */


/* =require "/core/core.js" */
/* =require "/ui/ui.js" */

if (typeof YAHOO === 'undefined' || !YAHOO) {
  throw new Error('YAHOO not declared (requires YUI)');
}

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
App.env = { };


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
  var HEADER_RTK_ERROR      = 'RTK-Error';

  App.AjaxFilter =
  {
    /**
     * Do some global pre-processing of a HTTP 2xx ajax response.
     *
     * @param  {Object}  o   The YAHOO.util.Connect response object.
     *
     * @return {Boolean}  Return true to proceed (eg. html replace), false to ignore the response.
     */
    onSuccess: function(o)
    {
      // show debug info from php code, the debug div exists only in dev environment
      var Dom = YAHOO.util.Dom, elDebug = Dom.get('AppAjaxFilterDebug');
      if (elDebug)
      {
        if (/^[^{]/.test(o.responseText))
        {
          elDebug.innerHTML = "<pre>\n" + o.responseText + "\n</pre>";
          elDebug.style.display = 'block';
        }
        else
        {
          elDebug.style.display = 'none';
        }
      }

      if (o.status === 404)
      {
        var message = o.getResponseHeader[HEADER_RTK_ERROR] || '("RTK-Error" header not set)';
        
        Core.warn('App.AjaxFilter() Caught a HTTP 404: "%s"', message);

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
    onFailure: function(o)
    {
      Core.log('AjaxFilter.onFailure(%o)', o);

      if (o.status === 500)
      {
        var message = o.getResponseHeader[HEADER_RTK_ERROR] || '("RTK-Error" header not set)';
        
        Core.warn('App.AjaxFilter() Caught a HTTP 500 error: "%s"', message);

        return false;
      }

      // let default failure handling of AjaxRequest etc (eg. show the error message in AjaxIndicator)
      return true;
    }
  };

}());


/**
 * Application specific UI helpers go here.
 *
 *   generateDialogMarkup()      Generate markup for a dialog, ready to use with Core.Ui.AjaxDialog
 */
App.Ui = {};


/**
 * Generate markup for a dialog, appends the div to the document,
 * ready for use with Core.Ui.AjaxDialog.
 * 
 * Options:
 * 
 *   title   Text to use as title 
 *   body    Text to insert in the dialog body
 *   icon    Type of icon (optional): info, success, warning, help, stop (see markup:ibox_rounded)
 *   
 *   buttons  Array of button definitions:
 *   
 *     label    Button label
 *     cust     Additional classes can be used to bind custom events or use
 *              the defaults from AjaxDialog ("JSDialogClose", "JSDialogSuccess", etc).
 *              For custom events, add the listener with AjaxDialog's on() method, eg:
 *               myDialog.on("do-something", function(e, el){...});
 *   
 * Simple example:
 *   d = new App.Ui.AjaxDialog(Core.Ui.Helper.generateDialogMarkup({ title: "foo", body:"loooool"}), { useMarkup: true }); d.show()
 * 
 * Loading message example:
 *   d = new App.Ui.AjaxDialog(Core.Ui.Helper.generateDialogMarkup({ title: "foo", body:'<span class="icon-16 icon-ajax">Processing...</span>'}), { useMarkup: true }); d.show()
 *   
 * Usage:
 * 
 *   dialogId = Core.Ui.Helper.generateDialog(...);
 *   dialog = new Core.Ui.AjaxDialog(dialogId, { useMarkup: true });
 *  
 * @return {String}  The generated dialog id (id of the outer div element)
 */
/*
App.Ui.generateDialogMarkup = function(options)
{
  var Dom = YAHOO.util.Dom,
      div = document.createElement("div"),
      i, id, buttons, sBoxIcon;

  options.title = options.title || "Note";
  options.buttons = options.buttons || [{ label: "Close", cust: "JSDialogClose" }];

  // box icon
  sBoxIcon = options.icon ? ' ibox-icon-'+options.icon : '';
  
  // create html for the buttons
  buttons = [];
  for (i = 0; i < options.buttons.length; i++) {
    var btnDef = options.buttons[i],
        btnClass = (btnDef.cust || '');
    buttons.push('<a href="#" class="uiIBtn uiIBtnGS ' + btnClass + '"><span>' + btnDef.label + '</span></a>');
  }
  buttons = buttons.join("&nbsp;");

  // dimensions for Core.Ui.AjaxDialog based on markup source (option srcMarkup)
  div.className = "dialog-width-400";
  div.style.display = "none";

  // generate markup here so it works on any page, without dependency
  // on static markup present in the page
  div.innerHTML = 
    '<div class="hd">' + options.title + '</div>' +
    '<div class="bd">' +
      '<div class="ibox-rc ibox-dialog'+sBoxIcon+'">' +
        '<div class="ibox-hd"></div>' +
        '<div class="ibox-bd">' +
          options.body +
        '</div>' +
        '<div class="ibox-ft"></div>' +
      '</div>' +
      '<div class="dialog-footer">' + buttons + '</div>' +
    '</div>';

  // generate unique id for our lovely div
  id = Dom.generateId(div);
  
  // add to document (note it is still hidden)
  document.body.insertBefore(div, document.body.firstChild);
  
  return id;
};
*/

/**
 * Global application object
 *
 */
(function() {

  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  Y.lang.augmentObject(App,
  {
    /**
     * EventDelegator.
     */
    bodyED:  null,

    /**
     * Constructor.
     *
     */
    init: function()
    {
      Core.log('App.init()');

      // global response filter
      if (Core.Ui && Core.Ui.AjaxRequest)
      {
        Core.Ui.AjaxRequest.responseFilter = App.AjaxFilter;
      }

      // initialize the page
      if (this.fnReady)
      {
        Core.log('App.ready()');
        this.fnReady();
      }
    },

    ready: function(fn)
    {
      this.fnReady = fn;
    },

    /**
     * Returns an EventDelegator instance for click events on the page body.
     *
     * @return  {Object}   Core.Ui.EventDelegator instance.
     */
    getBodyED: function()
    {
      return this.bodyED ? this.bodyED : (this.bodyED = new Core.Ui.EventDelegator(document.body, 'click'));
    },

    /**
     * Throws an exception, but since they don't seem to work,
     * throw an alert.
     *
     * @param {String}  Message, followed by optional arguments (sprintf style).
     */
    halt: function()
    {
      // throw exception doesnt work in Firefox?? wtf

      if (typeof(console) !== 'undefined' && typeof (console.error) === 'function') {
        console.error.apply(console.error, arguments);
      } else {
        alert(arguments[0]);
      }
    },

    /**
     * Log a warning message with special icon in Firebug's console,
     * in IE and Production environment, it will use Core.log() instead,
     * which will silently ignore the warning message.
     *
     * Note! warn() should not interrupt the application in production!
     *
     * @param {String}  Message, followed by optional arguments (sprintf style).
     */
    warn: function()
    {
      if (typeof(console) !== 'undefined' && typeof(console.warn) === 'function') {
        console.warn.apply(console.warn, arguments);
      } else {
        Core.log.apply(Core.log, arguments);
      }
    },

    /**
     * Throws an alert box, disable in production!
     */
    alert: function(s)
    {
      alert(s);
    }

   });

  Core.ready(function() { App.init(); });

}());


