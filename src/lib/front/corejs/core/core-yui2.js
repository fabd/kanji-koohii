/**
 * Provides additional helpers to YUI2 and include minimal YUI2 dependencies.
 * 
 *  YAHOO.bind(fn, scope [, args, ...] )
 *  
 *  YAHOO.Array  (see  http://developer.yahoo.com/yui/3/api/Array.html )
 *    Y.Array(o)
 *    Y.Array.each(array, fn [, context])
 *  
 *  YAHOO.util.Dom
 *    query(root, query)          Proxy for root.querySelector()
 *    queryAll(root, query)       Proxy for root.querySelectorAll()
 *    setClass()                  Adds or removes a class name from a given element.
 *    setStyles()
 *    toggle()
 * 
 *  YAHOO.bind(fn, scope [, args, ...] )
 * 
 * @author Fabrice Denis
 */
/*global YAHOO, Core */

/*! Copyright (c) 2010, Yahoo! Inc. All rights reserved. */

/* =require from "%YUI2%" */
/* =require "/yahoo-dom-event/yahoo-dom-event.js" */

/* These are for reference, but not enabled in the minimal build */

/* !require "/dragdrop/dragdrop-min.js" */
/* !require "/animation/animation-min.js" */
/* !require "/connection/connection-min.js" */
/* !require "/container/container-min.js" */

/**
 * Augment YUI 2.8.x with useful method/helpers, some of which are patched from YUI 3.0.0 Beta1.
 * 
 */
(function() {

  var Y = YAHOO,
      Dom = Y.util.Dom,
      ArrayNative = Array.prototype;
  
  /**
   * @name YAHOO.Dom
   */
  Y.lang.augmentObject(Dom,
  {
    /**
     * Proxy for element.querySelector().
     *
     * @param  {HTMLElement|string} root     Root element or selector (string)
     * @param  {string} query                Selector(s)
     * 
     * @return {HTMLElement|null}
     */
    query: function(root, query)
    {
      root = Y.lang.isString(root) ? document.querySelector(root) : root;
      return root.querySelector(query);
    },

    queryAll: function(root, query)
    {
      root = Y.lang.isString(root) ? document.querySelector(root) : root;
      return root.querySelectorAll(query);
    },

    /**
     * Adds or removes a class name from a given element.
     *
     * Simplifies switching a class name based on a boolean state.
     *
     * @param {HTMLElement|String}  el   The element to remove class from
     * @param {String} className         The class name to add or remove from the class attribute
     * @param {Boolean} state            True to add class name, false to remove class name
     */
    setClass: function(el, className, state)
    {
      console.assert(!!el, 'Dom::setStyles() el is invalid.');
      Dom[state ? 'addClass' : 'removeClass'](el, className);
    },

    /**
     * Toggle display of element.
     * 
     * @param {HTMLElement|string} el    Element or string id.
     * @param {Bool} display             True to display, false to hide    
     */
    toggle: function(el, display)
    {
      console.assert(!!el, 'Dom::toggle() el is invalid.');
      Dom.get(el).style.display = display ? "" : "none";
    }
  });

  if (!Y.bind)
  {
    /**
     * Returns a function that will execute the supplied function in the
     * supplied object's context, optionally adding any additional
     * supplied parameters to the END of the arguments the function
     * is executed with.
     *
     * In some cases it is preferable to have the additional arguments
     * applied to the beginning of the function signature.  For instance,
     * FireFox setTimeout/setInterval supplies a parameter that other
     * browsers do not.
     *   
     * Note: YUI provides a later() function which wraps setTimeout/setInterval,
     * providing context adjustment and parameter addition.  This can be 
     * used instead of setTimeout/setInterval, avoiding the arguments
     * collection issue when using bind() in FireFox.
     *
     * @param f {Function} the function to bind
     * @param c the execution context
     * @param args* 0..n arguments to append to the end of arguments collection
     * 
     * @return {function} the wrapped function
     */
    Y.bind = function(f, c) {
      var a = ArrayNative.slice.call(arguments, 2);
      return function () {
        return f.apply(c || f, ArrayNative.slice.call(arguments, 0).concat(a));
      };
    };
  }

}());


/**
 * Add useful helpers from YUI 3.0.0b1
 * 
 * YAHOO.Array 
 * 
 */
(function() {

  var Y = YAHOO,
      Native = Array.prototype;

  /**
   * YAHOO.Array from YUI 3.0.0 Beta 1 bugged in Safari 3! => Using plain old iteration code.
   * 
   * TODO: Check code from latest YUI 3 build
   * 
   * @param {Object} collection
   */
  Y.Array = function(o)
  {
    var a = [], i, l;
    for (i = 0, l = o.length; i < l; i = i + 1)
    {
      a.push(o[i]);
    }
    return a;
  };

  /**
   * Executes the supplied function on each item in the array.
   * 
   * The function is called with arguments: element, index, the_array
   * 
   * @method each
   * @param a {Array} the array to iterate
   * @param f {Function} the function to execute on each item
   * @param o Optional context object
   * @static
   * @return {YUI} the YUI instance
   */
  Y.Array.each = (Native.forEach) ?
    function (a, f, o) { 
      Native.forEach.call(a || [], f, o || Y);
      return Y;
    } :
    function (a, f, o) { 
      var l = (a && a.length) || 0, i;
      for (i = 0; i < l; i=i+1) {
          f.call(o || Y, a[i], i, a);
      }
      return Y;
    };

}());

