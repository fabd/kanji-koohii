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

  ready: function (f) {
    window.addEventListener("DOMContentLoaded", f);
  },
};

/* (refactoring) was "/core/toolkit.js" */

Core.Toolkit = {
  /**
   * Turns an object into its URL-encoded query string representation.
   *
   * Note the comment below, adding [] for arrays is only for use with php.
   *
   * @param {Object} obj   Parameters as properties and values
   */
  toQueryString: function (obj, name) {
    var Lang = YAHOO.lang;

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

Core.Ui = {};
Core.Widgets = {};

export default Core;
