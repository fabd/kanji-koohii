/**
 * Core framework.
 *
 * Core is the global namespace that acts as a wrapper around library specific code.
 *
 * Core methods
 *   make()           OOP, returns constructor for a base class
 *   extend()         OOP, returns a constructor for an extended class
 *   ready()          Sets window onload code
 *
 */

const YAHOO = window.YAHOO;

var Core = {
  /**
   * A constructor function to create a new class.
   *
   * Examples:
   *   Core.Ui.FooWidget = Core.make();
   *
   * @param {Object} px   Optional prototype object containing properties and methods
   * @return {Function}   Class constructor that will call init() method when instanced
   */
  make: function (px) {
    var fn = function () {
      return this.init.apply(this, arguments);
    };

    // optional: set prototype for the new class
    if (px) {
      fn.prototype = px;
    }

    return fn;
  },

  /**
   * Create a child class from a base class and optional properties/methods
   *
   * Example:
   *
   *   var Human = Core.make();
   *   Human.prototype = {
   *     init: function() {
   *       // ...
   *     }
   *   };
   *
   *   var SuperHuman = Core.make();
   *   Core.extend(SuperHuman, Human, {
   *     init: function() {
   *       // call parent constructor
   *       SuperHuman.superclass.init.apply(this, arguments);
   *     }
   *   });
   *
   * See YAHOO.lang.extend example http://developer.yahoo.com/yui/examples/yahoo/yahoo_extend.html
   *
   * @param {Function} subc     Sub class constructor
   * @param {Function} superc   Base class constructor
   * @param {Object} overrides  Additional properties/methods to add to the child prototype
   */
  extend: function (subc, superc, overrides) {
    YAHOO.lang.extend(subc, superc, overrides);
  },

  ready: function (f) {
    window.addEventListener("DOMContentLoaded", f);
  },
};

/* (refactoring) was "/core/toolkit.js" */

var Y = YAHOO,
  Lang = Y.lang;

Core.Toolkit = {
  /**
   * Turns an object into its URL-encoded query string representation.
   *
   * Note the comment below, adding [] for arrays is only for use with php.
   *
   * @param {Object} obj   Parameters as properties and values
   */
  toQueryString: function (obj, name) {
    var i,
      l,
      s = [];

    if (Lang.isNull(obj) || Lang.isUndefined(obj)) {
      return name ? encodeURIComponent(name) + "=" : "";
    }

    if (Lang.isBoolean(obj)) {
      obj = obj ? 1 : 0;
    }

    if (Lang.isNumber(obj) || Lang.isString(obj)) {
      return encodeURIComponent(name) + "=" + encodeURIComponent(obj);
    }

    if (Lang.isArray(obj)) {
      // add '[]' here for php to receive an array
      name = name + "[]";
      for (i = 0, l = obj.length; i < l; i++) {
        s.push(Core.Toolkit.toQueryString(obj[i], name));
      }
      return s.join("&");
    }

    // now we know it's an object.
    var begin = name ? name + "[" : "",
      end = name ? "]" : "";
    for (i in obj) {
      if (obj.hasOwnProperty(i)) {
        s.push(Core.Toolkit.toQueryString(obj[i], begin + i + end));
      }
    }

    return s.join("&");
  },
};

/**
 * Core.Ui Helpers
 *
 * These are global helpers related to the DOM and user interface.
 */
Core.Ui = {};
Core.Widgets = {};

var Y = YAHOO,
  Dom = Y.util.Dom;

Core.Ui.Helper = {
  /* OBSOLETE
     * Return parameters and values that are passed through
     * the HTML Element class names.
     * 
     * <pre>
     *   &lt;div class="module module-id-1 module-status-off"&gt; ... &lt;/div&gt;
     *   // => { id: "1", status: "off" }
     * </pre>
     * 
     * @param  {HTMLElement} el     The element
     * @param  {String}   name      The base class name (without dash suffix!)
     * 
     * @return {Object}
    getParams: function(el, name)
    {
      var re = new RegExp("(?:^|\\s)" + name + "-([^-]+)-(\\w+)", "g"),
          obj = {},
          a;
      
      while ((a = re.exec(el.className)))
      {
        var prop = a[1], value = a[2];
        obj[prop] = value;
      }
      
      return obj;
    },
     */

  /**
   * Open an AjaxDialog from a given url, or the url found in the clicked link.
   *
   * This is a generic helper for the most common cases where no complicated
   * handling needs to be done with the dialog response.
   *
   * Options
   *   srcUrl     {String}        Url to load the dialog from
   *   srcHref    {HTMLElement}   Will use the href attribute of the element as srcUrl
   *
   *   successUrl {String}        Load given url on the dialog success event (note: this will override
   *                              events.onDialogSuccess, don't use both)
   *
   * Advanced:
   *   events     {Object}        (optional) AjaxDialog listeners, see Core.Ui.AjaxDialog
   *
   * @param  {Object} options
   *
   * @return {boolean}        Always returns false (return fn() shortcut, REFACTOR)
   */
  openAjaxDialog: function (options) {
    var o = options,
      dlgOptions = { srcUrl: null },
      ajaxDialog;

    console.log("openAjaxDialog options: ", o);

    dlgOptions.requestUri = o.srcHref
      ? Dom.getAttribute(o.srcHref, "href")
      : o.srcUrl;
    dlgOptions.timeout = o.timeout ? o.timeout : null; // use default timeout if not specified in options
    dlgOptions.events = options.events ? options.events : {};

    if (o.successUrl) {
      dlgOptions.events.onDialogSuccess = function () {
        window.location.href = o.successUrl;
      };
    }

    ajaxDialog = new Core.Ui.AjaxDialog(null, dlgOptions);
    ajaxDialog.show();

    return false;
  },

  /**
   * Insert node as first child of the document, _EXCEPT_ for IE, insert
   * as the first child of the #ie div (which should be first child of document.body).
   *
   * This ensures that styles targetting IE (#ie .foobar { ... } ) will work
   * on dialogs and other elements dynamically inserted into the page.
   *
   * @param {HTMLElement} node
   */
  insertTop: function (node) {
    var elParent = Dom.get("ie") || document.body;
    elParent.insertBefore(node, elParent.firstChild);
  },
};

/**
 * Core.Ui Helpers
 *
 * These are global helpers related to the DOM and user interface.
 */
Core.Ui.Mobile = {
  /**
   * Checks whether viewport width is below threshold where we want to use optimized
   * styles and behaviours (eg. edge to edge dialogs, larger buttons).
   *
   * @return {boolean}
   */
  isMobile: function () {
    return window.innerWidth <= 720;
  },
};

export default Core;
