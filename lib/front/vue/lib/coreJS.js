/**
 * coreJS
 *
 * Note: this is kind of a mess. Currently working usage are documented in coreJS.README.md
 *
 * This is meant to provide super simple jQuery-like API for misc. DOM manipulation in VueJs
 * components.
 * 
 * Over time, the plan is to phase out the old YUI 2 based code, and move some of the basic 
 * utility from /lib/front/corejs to /lib/vue/lib/coreJs.js (this).
 * 
 *
 * Usage:
 *
 *   See coreJS.README.md
 *
 *
 * Methods:
 *
 * 	 each(fn)
 *    
 * 	 includes(element [, index])
 *
 *   css(prop)                         ... get/set inline styles
 *    css(prop, value)
 *    css({ prop1: value, ...})
 *
 *   on(event, callback)
 *    on([event1, event2 ...], fn)
 *    
 *   off('click')                      ...
 *    off('click', fn)                 
 *    off([event1, event2 ...], fn)    
 *    off(fn)
 *    
 *   once(event, callback)             ... Call handler once, then remove it
 *
 *
 */

// import {EventCache} from './coreJS/EventCache.js'

const ArrayProto = Array.prototype

// let nodeError  = new Error( 'Passed arguments must be of Node' )

function isNode(el) {	return el instanceof window.Node; }
function isNodeList(el) {	return el instanceof window.NodeList || el instanceof NodeList || el instanceof HTMLCollection || el instanceof Array; }

// misc helpers
const isObject    = (arg) => typeof arg === 'object'
const isString    = (arg) => typeof arg === 'string'
const isUndefined = (arg) => typeof arg === 'undefined'
const isDefined   = (o, p) => (p in o)

// version de vue-strap
let Events = []

// development helpers
const assert = (process.env.NODE_ENV !== 'production')
  ? function(truthy, reason) {
      if (true !== truthy) {
        console.error('assert() failed because ... %s', reason || '{ no reason given in assert() call }')
      }
    }
  : function() { }

const error = (process.env.NODE_ENV !== 'production')
  ?	function() { console.error.apply(console, arguments); console.trace(); }
  : function() { }

const log = (process.env.NODE_ENV !== 'production')
  ? function() { console.log.apply(console, arguments) }
  : function() { }


function flatten(arr) {
  let list = [], i2, l2
  ArrayProto.forEach.call(arr, el => {
    if (isNode(el)) {
      if (list.indexOf(el) < 0) list.push(el)
    } else if (isNodeList(el)) {
      // for (let id in el) list.push(el[id])
			for (i2 = 0, l2 = el.length; i2 < l2; i2++) list.push(el[i2]);
    } else if (el !== null) {
      arr.get = NL.get
      arr.set = NL.set
      arr.call = NL.call
      return arr
    }
  })
  return new NodeListJS(list)
}

function NodeListJS(selector, context) {
	var i = 0, l, nodes;

  // console.log('NodeList(%o) nodes.length=%d', args, nodes.length);

  // window is not instanceof Node, has "length", but doesn't behave like an array
  if (selector === window) {
    nodes = [window]
  }
  else if (isString(selector)) {

		// TODO : use getElementById()...

		nodes = (context || document).querySelectorAll(selector);
	}
	// it's an array or some kind of collection (including *this*)
	else if (selector && !isNode(selector) && 'length' in selector) {
		nodes = selector;
	}
	// assume it's a Node
	else {
		this[0] = selector;
		this.length = 1;
		return this;
	}

	for (l = this.length = nodes.length; i < l; i++) {
		this[i] = nodes[i];
	}
}

let NL = NodeListJS.prototype = {

  each: function() {
    ArrayProto.forEach.apply(this, arguments);
    return this;
  },

	// use native if present
  includes: ArrayProto.includes || function includes(element, index) {
		return this.indexOf(element, index) > -1;
	},

  // Bind one or more event types to a listener, for a SINGLE element
  on: function(events, callback) {
  	let el = this[0]
    assert(el === window || isNode(el))
    if (isString(events)) { events = [events] }

    events.forEach(event => {
      el.addEventListener(event, callback, false)
      Events.push( {el: el, type: event, fn: callback })
    })
  },

  off: function(events, callback) {
    let el = this[0]
    assert(el === window || isNode(el))

    // .off('click')
    if (isString(events)) {
      events = [events]
    }
    // .off(callback)
    else if (events instanceof Function) {
      callback = events
      events = null
    }
    // .off()
    else {
      assert(arguments.length === 0)
    }
    
    Events = Events.filter(e => {
      if (e && e.el === el && (!callback || callback === e.fn) && (!events || events.indexOf(e.type) > -1)) {
        e.el.removeEventListener(e.type, e.fn)
        return false
      }
      return true
    })

    return this
  },

  // .once('transitionend', fn)
  once: function(event, fn) {
  	let that = this
  	let el = this[0]
    assert(fn instanceof Function && (el === window || isNode(el)))
	  let listener = function() {
	  	// console.log('called once just now');
	    fn && fn.apply(this, arguments)
	    that.off(event, listener)
	  }
	  this.on(event, listener)
	},

	/* DOM utility */

  // get a single style / set one (string) or more (object) styles
  css: function(...args)
  {
    let element = this[0]
    let styles

    if (isString(args[0])) {
      let prop = args[0]
      
      // return a single style
      if (args.length === 1) {
        assert(isDefined(element.style, prop), 'invalid property name')
        return element.style[prop]
      }

      // set a single style
      assert(!isUndefined(args[1]))
      styles = {}
      styles[args[0]] = args[1]
    }
    else {
      styles = args[0]
    }

    assert(isObject(styles), 'invalid argument(s)')

    // set one or more styles
    for (let prop in styles) {
      element.style[prop] = styles[prop]
    }
  }

};

Object.getOwnPropertyNames( ArrayProto ).forEach( function( key ) {
	if( key !== 'join' && key !== 'copyWithin' && key !== 'fill' && NL[ key ] === undefined ) {
		NL[ key ] = ArrayProto[ key ];
	}
});

// fabd-- : ES6 for ... of (we don't use it)
// if( window.Symbol && Symbol.iterator ) {
// 	NL[ Symbol.iterator ] = NL.values = ArrayProto[ Symbol.iterator ];
// }

function setterGetter( prop ) {
	if (div[prop] instanceof Function) {
		NL[prop] = function() {
			var el = this[0], result;
			assert(!!el, "calling method on an invalid element")

			if (el && el[prop] instanceof Function) {
				result = el[prop].apply(el, arguments);
			}
			return !isUndefined(result) ? flatten(result) : this;
		}
	}
	else {
		Object.defineProperty( NL, prop, {
      get () {
        let arr = []
        this.each(el => {
          if (el !== null) { el = el[prop] }
          arr.push(el)
        })
        return flatten(arr)
      },
      set (value) {
        this.each(el => {
          if (el && prop in el) { el[prop] = value }
        })
      }
		});
	}
}

// debug
//var propnames = [];
//for (prop in div) { propnames.push(prop); }
//console.log("setterGetter() on " + propnames.join(", "));

let div = document.createElement( 'div' )

for (let prop in div) setterGetter(prop)

div = null;

function CoreJS(selector, context) { return new NodeListJS(selector, context) }

export default CoreJS
