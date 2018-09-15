/**
 * A small jquery-like utility library for the Vue front-end.
 *
 * BROWSER SUPPORT
 *
 *   Recent versions of Edge, Chrome, Safari, iOS Safari, Chrome for Android (as per caniuse.com)
 *
 *   NOT supported:  IE (any version), Opera Mini.
 *
 *   August 2018:  EDGE (2.97%)   IE (2.03%, of which 11=55%, 10=42%)   Samsung Internet (1.06%)
 *
 * 
 * NOTES
 * 
 *   - chaining a method is allowed only once after the constructor
 *      
 *
 * USAGE
 *
 *   Import:
 *   
 *     import Dom from 'dom.js'
 *
 * 
 *   To get HTMLElement use [0]:
 *   
 *     let elContainer = Dom('.box').closest('.container')[0]
 *
 * 
 *   To check for element, use [0]:
 *
 *     if (Dom('#some-component')[0]) { ... }
 *
 * 
 *   Convention: use a $ prefix to distinguish HTMLElement itself from the Dom() instance:
 *
 *     let $box = Dom('.box')
 *     $box.css('width') = '20px'
 *     $box.on('click', (ev) => { console.log("clicked el %o", ev.target) })
 *
 *
 * IMPORT AS 'Dom'
 *
 *   el(i = 0)                         ... return the underlying DOM element (index defaults to 0)
 *
 *   closest(selector)                 ... find the first ancestor matching selector
 *
 *   css(prop)                         ... get/set inline styles
 *    css(prop, value)
 *    css({ prop1: value, ...})
 *
 *   on(event, callback)
 *    on([event1, event2 ...], fn)
 *
 *   off('click')                      ... unbind events and/or listeners
 *    off('click', fn)
 *    off([event1, event2 ...], fn)
 *    off(fn)
 *
 *   once(event, callback)             ... call handler once, then remove it
 *
 *
 * IMPORT METHODS
 *
 *   getStyle()
 *   insertAfter(newNode, refNode)     ... insert newNode as next sibling of refNode
 *   remove()
 *
 */

// import { isFunction, isNode, isObject, isString } from 'lib/koohii/lang.js'
import Lang from 'lib/koohii/lang.js'
const
  isArray     = Lang.isArray,
  isDefined   = Lang.isDefined,
  isFunction  = Lang.isFunction,
  isNode      = Lang.isNode,
  isObject    = Lang.isObject,
  isString    = Lang.isString,
  isUndefined = Lang.isUndefined


// aliases 
const document = window.document
const documentElement = document.documentElement

// functions
const inDocument = (el) => documentElement.contains(el)

// string constants
const PARENT_NODE = 'parentNode'
const NODE_TYPE = 'nodeType'
const TAG_NAME = 'tagName'

//
let Events = []

function DomJS(selector, context)
{
  let nodes

  // window is not instanceof Node, has "length", but doesn't behave like an array
  if (selector === window) {
    nodes = [window]
  }
  else if (isString(selector)) {
    nodes = (context || document).querySelectorAll(selector)
  }
  // it's an array or some kind of collection (including *this*)
  else if (selector && !isNode(selector) && 'length' in selector) {
    nodes = selector
  }
  // assume it's a Node
  else {
    this[0] = selector;
    this.length = 1
    return this
  }

  for (let i = 0, l = this.length = nodes.length; i < l; i++) {
    this[i] = nodes[i]
  }
}

DomJS.prototype = {

  /**
   * Retrieve one of the elements matched by the selector.
   *
   * NOTE! Unlike jQuery's "get", el() by default returns the first element, for convenience:
   *
   *   if (Dom('.box').el()) { // element is found... }
   *
   *
   * @param {Number} i  (optional) Index of element to retrieve
   * @return {HTMLElement}   The underlying DOM element
   */
  el(i) {
    return this[i || 0]
  },

  // TODO? return array of DOM nodes
  // els() {
  // }

  /**
   * Returns first ancestor matching selector, or null.
   * 
   * @param {String} selector 
   * @return {HTMLElement | null}
   */
  closest(selector) {
    
    console.assert(isString(selector), "closest() : selector is invalid")

    let el = this[0]
    console.assert(isNode(el), "closest() : el is invalid")

    let fn = (node) => node.matches && node.matches(selector)

    if (!el || !inDocument(el)) {
      console.warn('closest() invalid node')
      return null
    }

    while ((el = el.parentElement || el.parentNode)) {
      if (fn(el)) return el
    }

    return null
  },

  /**
   * Bind one or more event types to a listener, for a SINGLE element
   * 
   * @param  {String |_Array}   events   One or more event types, eg. ['click', 'focus']
   * @param  {Function} callback
   */
  on(events, callback) {   
    let el = this[0]
    console.assert(el === window || isNode(el), "on() el is invalid")
    if (isString(events)) { events = [events] }

    events.forEach(event => {
      el.addEventListener(event, callback, false)
      Events.push( {el: el, type: event, fn: callback })
    })
  },

  /**
   * Detach event listeners.
   * 
   * @param {String | Array | Function} events  One or more event types, OR the listener to detach (one argument)
   * @param {Function} callback                 (optional) Detach only events matching this callback
   */
  off(events, callback) {
    let el = this[0]
    console.assert(el === window || isNode(el), "off() : el is invalid")

    // .off('click')
    if (isString(events)) {
      events = [events]
    }
    // .off(callback)
    else if (isFunction(events)) {
      callback = events
      events = null
    }
    // .off()
    else {
      console.assert(arguments.length === 0)
    }
    
    Events = Events.filter(e => {
      if (e && e.el === el && (!callback || callback === e.fn) && (!events || events.indexOf(e.type) > -1)) {
        e.el.removeEventListener(e.type, e.fn)
        return false
      }
      return true
    })
  },

  // 
  /**
   * Fire an event only once, then remove said event.
   *
   * Example:
   * 
   *   el.once('transitionend', fn)
   *   
   * @param {String} event
   * @param {Function} fn   Callback
   */
  once(event, fn) {
    let that = this
    let el = this[0]
    console.assert(isFunction(fn), "once() : fn is not a function")
    console.assert(el === window || isNode(el), "once() : el is invalid")
    let listener = function() {
      // console.log('called once just now');
      fn && fn.apply(this, arguments)
      that.off(event, listener)
    }
    this.on(event, listener)
  },

  /**
   * Get / set inline style(s).
   *
   * Usage:
   *
   *   css(prop)                       Get inline style
   *   css(prop, value)                Set one inline style
   *   css({ prop1: value, ...})       Set multiple inline styles
   *    
   */
  css(...args) {
    let element = this[0]
    let styles

    if (isString(args[0])) {
      let prop = args[0]
      
      // css('prop')
      if (args.length === 1) {
        console.assert(isDefined(element.style, prop), 'invalid property name')
        return element.style[prop]
      }

      // css('prop', value)
      console.assert(!isUndefined(args[1]))
      styles = {}
      styles[args[0]] = args[1]
    }
    else {
      styles = args[0]
    }

    console.assert(isObject(styles), 'invalid argument(s)')

    // set one or more styles
    for (let prop in styles) {
      element.style[prop] = styles[prop]
    }
  },


  // insertLast(newNode) {
    // "if the reference node is null, the specified node is added to the end of the list
    //  of children of the specified parent node."
    // refNode[PARENT_NODE].insertBefore(newNode, null)
  // },

  /**
   * Removes the node from the tree it belongs to.
   *
   * @return {HTMLElement} Returns removed node.
   */
  remove() {
    const node = this[0]
    console.assert(isNode(node), "remove() : node is invalid")
    return node[PARENT_NODE].removeChild(node)
  }
}


const factory = function(selector, context) { return new DomJS(selector, context) }


/**
 * USAGE
 *
 *   add(el, 'foo')
 *   add(el, 'foo bar')
 *   add(el, ['foo', 'bar'])
 *
 *   remove(el, 'foo')
 *   remove(el, 'foo bar')
 *   remove(el, ['foo', 'bar'])
 * 
 *   toggle(el, name)
 *   toggle(el, name, force)
 *
 *
 * COMPATIBILITY
 *
 *   - IE10/11 : does not support add/remove of multiple classes (only the 1st one)
 * 
 */
export const classList = {

  _set(el, names, add) {
    console.assert(isNode(el), "classList.add/remove : invalid node")
    console.assert(isString(names) || isArray(names), "classList : class must be a String or Array")
    if (!el) return
    let classes = isString(names) ? names.split(' ') : /* assumed Array */ names
    // FIXME? IE10/11 does not support multiple classes for add/remove (loop?)
    el.classList[add ? 'add' : 'remove'](...classes)
  },

  add(el, names) {
    this._set(el, names, true)
  },

  remove(el, names) {
    this._set(el, names, false)
  },

  toggle(el, name, force) {
    // NOTE: doing it this way supports IE10/11 lack of support for "force"
    if (arguments.length > 2) {
      this._set(el, [name], !!force)
    }
    else {
      el.classList.toggle(name)
    }
  }
}


/**
 * Inserts the new node as the next sibling of the reference node
 *
 * @param {HTMLElement} refNode
 * @param {HTMLElement} newNode
 * @return {HTMLElement} The node that was inserted (or null if insert fails)
 */
export function insertAfter(newNode, refNode) {
  console.assert(isNode(newNode) && isNode(refNode), "insertAfter() : newNode and/or refNode is invalid")
  if (refNode.nextSibling) {
    return refNode[PARENT_NODE].insertBefore(newNode, refNode.nextSibling)
  } else {
    return refNode[PARENT_NODE].appendChild(newNode)
  }
}

/**
 * getStyle()
 * 
 * @param  {HTMLElement} element
 * @param  {String} styleName    MUST be camelCase form
 */
export function getStyle(element, styleName) {
  console.assert(element && styleName, "getStyle() : invalid arguments")
  // styleName = camelCase(styleName);
  if (styleName === 'float') {
    styleName = 'cssFloat';
  }
  try {
    let computed = document.defaultView.getComputedStyle(element, '')
    return element.style[styleName] || computed ? computed[styleName] : null
  } catch (e) {
    return element.style[styleName]
  }
}


export { factory as default }
