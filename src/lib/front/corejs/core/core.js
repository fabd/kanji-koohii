/**
 * Core framework.
 * 
 * Core is the global namespace that acts as a wrapper around library specific code.
 * 
 * Core methods
 *   bind()           Create a closure to preserve execution context
 *   make()           OOP, returns constructor for a base class
 *   extend()         OOP, returns a constructor for an extended class
 *   assert()         Logs a message if condition failed.
 *   warn()           Log a warning message (maps to Firebug console.warn() if present)
 *   halt()           Throws an error message (maps to Firebug console.error() if present).
 *   ready()          Sets window onload code 
 *   log()            Log message to console (maps to Firebug console.log() if present)
 * 
 * @author  Fabrice Denis
 */
/*jslint forin: true */
/*global YAHOO, window, YAHOO, alert, console, document */

/* =require from "%CORE%" */
/* =require "/core/core-yui2.js" */

var Core =
{
  /**
   * Helper to bind function with arguments always appended to
   * the END of the argument collection.
   * 
   * See toolkit.js 
   */
  bind: function(fn, context, args)
  {
    // $.proxy.apply($, arguments);
    return YAHOO.bind.apply(YAHOO, arguments);
  },
  
  /**
   * A constructor function to create a new class.
   * 
   * Examples:
   *   Core.Ui.FooWidget = Core.make();
   *   
   * @param {Object} px   Optional prototype object containing properties and methods
   * @return {Function}   Class constructor that will call init() method when instanced
   */
  make: function(px)
  {
    var fn = function() {
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
  extend: function(subc , superc , overrides)
  {
    YAHOO.lang.extend(subc, superc, overrides);
  },

  /**
   * Set the document onload event.
   *
   */
  ready: function(f)
  {
    YAHOO.util.Event.onDOMReady(f);
  }
  
};

/* =require "/core/toolkit.js" */

// shortcut to test & learn YUI in Firebug's console
var Y = YAHOO;

