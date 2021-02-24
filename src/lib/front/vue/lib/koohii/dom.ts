/**
 * A tiny jQuery-style library for DOM manipulation.
 *
 * BROWSER SUPPORT
 *
 *   Modern browsers, including EDGE.
 *
 *
 * CONSTRUCTOR
 *
 *   For convenience, chaining a method is allowed *once* after the constructor:
 *
 *     $('#help-box').css('display', 'block')
 *
 *   To call more than one method, assign the result to a temporary variable, eg:
 *
 *     const $app = $('#app')
 *     $app.css('background', 'powderblue')
 *     $app.on('click', evt => { console.log('clicked %o', evt.target) })
 *
 *
 * EXAMPLES
 *
 *   Import:
 *
 *     import $ from 'dom'
 *
 *   Using the constructor with a selector:
 *
 *     let el = $('.box')
 *     let articles = $('article', '#main-content')  // optional context element
 *
 *   Two ways of obtaining the actual element reference:
 *
 *     let $el = $('.box')[0]
 *     let $el = $('.box').el()
 *
 *   Using the constructor, from an element or `window` reference:
 *
 *     $(element).css('display', 'block')
 *     $(window).on('DOMContentLoaded', function() { ... })
 *
 *   Convention: use a `$` prefix to distinguish DomJS instance from the element:
 *
 *     let $box = $('.box')
 *     $box.css('width') = '20px'
 *     $box.on('click', (ev) => { console.log("clicked el %o", ev.target) })
 *
 *
 * METHODS
 *
 *   constructor(selector, context)    ... query elements, optional root element
 *
 *   el(i = 0)                         ... return the underlying DOM element
 *
 *   closest(selector)                 ... find the first ancestor matching selector
 *
 *   css(prop)                         ... get/set inline styles
 *    css(prop, value)
 *    css({ prop1: value, ...})
 *
 *   each((el, index) => {})           ... iterate over selected elements
 *
 *   on(type, listener)                ... bind one or more listeners to .el(0)
 *    on([type1, type2, ...], fn)
 *
 *   off(type)                         ... unbind events by type(s) or listener
 *    off([type1, type2, ...])
 *    off(listener)
 *
 *   once(type, listener)              ... call handler once, then remove it
 *
 *   remove(node)                      ... same as `node.parentNode.removeChild(node)`
 *
 *
 * NAMED IMPORTS
 *
 *   getStyle()
 *   insertAfter(newNode, refNode)     ... insert newNode as next sibling of refNode
 *   toggleClass()
 *   offsetTop()
 *
 */

// types
type StringHash = { [key: string]: string };
type StringOrStringArray = string | string[];

// helpers
const inDocument = (el: Node | null) => document.documentElement.contains(el);
const isArray = (o: any): boolean => Array.isArray(o);
const isFunction = (f: any): f is Function => typeof f === "function";
const isNode = (el: any): boolean => el instanceof Node;
const isString = (s: any): s is string => typeof s === "string";
const isWindow = (o: any): o is Window => o === window;

//
type $Event = {
  el: Element;
  type: string;
  fn: EventListener;
};
let $Events: $Event[] = [];

/*
export interface DomJSInterface<EL> {
  el(i?: number): EL;

  closest(selector: string): Element | null;

  css(props: string, value: string): void;
  css(props: string): string;
  css(props: StringHash): void;

  each(callback: { (element: EL, index: number): false | void }): void;

  on(events: string | string[], callback: EventListener): void;

  off(events: string | string[], callback?: EventListener): void;

  once(event: string, fn: EventListener): void;

  remove(): Node | null;

  toArray(): EL[];
}
*/

type DomJSSelector = string | Window | Node;

class DomJS<EL extends Element> implements ArrayLike<EL> {
  // ArrayLike
  length: number;
  [n: number]: EL;

  constructor(selector: DomJSSelector, context: Element) {
    let nodes: ArrayLike<EL>;

    console.assert(!context || isNode(context), "DomJS(): invalid `context` argument");

    if (isString(selector)) {
      nodes = (context || window.document).querySelectorAll(selector);
    }
    // window is not instanceof Node, has "length", but doesn't behave like an array
    else if (isWindow(selector)) {
      nodes = [window as any];
    }
    // assume it's a Node
    else {
      this[0] = selector as EL;
      this.length = 1;
      return this;
    }

    for (let i = 0, l = (this.length = nodes.length); i < l; i++) {
      this[i] = nodes[i];
    }
  }

  /**
   * Retrieve one of the elements matched by the selector.
   *
   * For convenience, el() by default returns the first element.
   *
   *   if ($('.box').el()) {
   *     // element is found...
   *   }
   *
   */
  el(i?: number) {
    return this[i || 0] as EL;
  }

  /**
   * Returns first ancestor matching selector, or null.
   *
   * NOTE!  Unlike Element.closest() method, this function does NOT
   *        return the original element.
   *
   *   const ul = $('.TodoList-item').closest('ul') as HTMLUListElement
   */
  closest(selector: string): Element | null {
    console.assert(isString(selector), "closest() : selector is invalid");

    let el = this[0] as Element;
    console.assert(isNode(el), "closest() : el is invalid");

    if (!inDocument(el)) {
      return null;
    }

    let matchesSelector = (el: Element) => el.matches(selector);

    while ((el = el.parentElement || (el.parentNode as Element))) {
      if (matchesSelector(el)) return el;
    }

    return null;
  }

  /**
   * Iterate over collection returned by the constructor.
   *
   * Return explicit `false` to end the loop.
   *
   * Example:
   *
   *   $('#todolist li').each( (el, index) => { console.log(el, index) })
   */
  each(callback: { (element: EL, index: number): false | void }): void {
    for (let i = 0, l = this.length; i < l; i++) {
      if (callback(this[i] as EL, i) === false) break;
    }
  }

  /**
   * Bind one or more event types to a listener, for a SINGLE element
   *
   * @param  events   One or more event types, eg. ['click', 'focus']
   * @param  callback
   */
  on(events: string | string[], callback: EventListener) {
    const el = this[0];
    // console.assert(el === window || isNode(el), "on() el is invalid");

    if (isString(events)) {
      events = [events];
    }

    events.forEach((event) => {
      el.addEventListener(event, callback, false);
      $Events.push({ el, type: event, fn: callback });
    });
  }

  /**
   * Detach event listeners.
   *
   * @param events   One or more event types, OR the listener to detach (one argument)
   * @param callback   (optional) Detach only events matching this callback
   */
  off(events: string | string[] | EventListener | null) {
    let callback: EventListener;
    const el = this[0];
    // console.assert(el === window || isNode(el), "off() : el is invalid");

    // .off('click')
    if (isString(events)) {
      events = [events];
    }
    // .off(callback)
    else if (isFunction(events)) {
      callback = events as EventListener;
      events = null;
    }
    // .off()  (remove all events)

    $Events = $Events.filter((e) => {
      if (
        e.el === el &&
        (!callback || callback === e.fn) &&
        (!events || (events as string[]).includes(e.type))
      ) {
        e.el.removeEventListener(e.type, e.fn);
        return false;
      }
      return true;
    });
  }

  /**
   * Fire an event only once, then remove said event.
   *
   * Example:
   *
   *   Dom(el).once('transitionend', fn)
   *
   */
  once(event: string, fn: EventListener) {
    // console.assert(isFunction(fn), "once() : fn is not a function");
    // console.assert(el === window || isNode(el), "once() : el is invalid");

    const listener = (...args: any[]) => {
      (fn as Function).apply(this, args);
      this.off(listener);
    };

    this.on(event, listener);
  }

  /**
   * Set a single css property.
   *
   * @param props   The CSS property name (not camelCase!)
   * @param value
   */
  /**
   * Get / set inline style(s).
   *
   * Usage:
   *
   *   css('prop')                     Get inline style
   *   css('prop', value)              Set one inline style
   *   css({ prop1: value, ...})       Set multiple inline styles
   *
   * @return string
   *
   */
  /**
   * Set one or more CSS properties with object notation.
   *
   */
  css(props: string | StringHash, value?: string): any {
    const element = (this[0] as any) as HTMLElement;
    let styles: StringHash;

    if (isString(props)) {
      // css('prop')
      if (arguments.length === 1) {
        // console.assert(props in element.style, "invalid property name");
        return element.style.getPropertyValue(props);
      }

      // css('prop', value)
      styles = { [props]: value as string };
    } else {
      styles = props;
    }

    // set one or more styles
    for (const prop in styles) {
      element.style.setProperty(prop, styles[prop]);
    }
  }

  /**
   * Removes the node from the tree it belongs to.
   *
   * @return    Returns removed node, or null
   */
  remove(): Node | null {
    const node = this[0] as Node;
    return (node.parentNode && node.parentNode.removeChild(node)) || null;
  }

  /**
   * Retrieve all the elements in the set, as an array.
   *
   */
  toArray(): EL[] {
    return Array.prototype.slice.call(this);
  }
}

const factory = <EL extends Element>(
  selector: DomJSSelector,
  context?: any
) => {
  return new DomJS<EL>(selector, context);
};

export { factory as default };

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
  _set(el: Node, names: StringOrStringArray, add: boolean) {
    console.assert(isNode(el), "classList.add/remove : invalid node");
    console.assert(
      isString(names) || isArray(names),
      "classList : class must be a String or Array"
    );
    if (!el) return;
    let classes: string[] = isString(names)
      ? names.split(" ")
      : /* assumed Array */ names;
    // FIXME? IE10/11 does not support multiple classes for add/remove (loop?)
    (el as HTMLElement).classList[add ? "add" : "remove"](...classes);
  },

  add(el: Node, names: StringOrStringArray) {
    this._set(el, names, true);
  },

  remove(el: Node, names: StringOrStringArray) {
    this._set(el, names, false);
  },

  toggle(el: Node, name: string, force: boolean) {
    // NOTE: doing it this way supports IE10/11 lack of support for "force"
    if (arguments.length > 2) {
      this._set(el, [name], !!force);
    } else {
      (el as HTMLElement).classList.toggle(name);
    }
  },
};

// --------------------------------------------------------------------
// misc DOM helpers
// --------------------------------------------------------------------

/**
 * Returns a single Element, or null.
 *
 * This is a helper for other methods, to accept a node either as a DOM
 * node reference, or a selector string.
 */
export function getNode(sel: Element | string): Element | null {
  return (isString(sel) && document.querySelectorAll(sel)[0]) || null;
}

/**
 * Inserts the new node as the next sibling of the reference node.
 *
 * @returns The appended child node
 */
export function insertAfter(newNode: Element, refNode: Element | string) {
  refNode = getNode(refNode)!;
  console.assert(
    isNode(newNode) && isNode(refNode),
    "insertAfter() : newNode and/or refNode is invalid"
  );
  if (refNode.nextSibling) {
    return refNode.parentElement!.insertBefore(newNode, refNode.nextSibling);
  } else {
    return refNode.parentElement!.appendChild(newNode);
  }
}

/**
 * getStyle()
 *
 *   styleName    MUST be camelCase form!
 */
export function getStyle(element: HTMLElement, styleName: string) {
  console.assert(!!(element && styleName), "getStyle() : invalid arguments");

  // styleName = camelCase(styleName);
  if (styleName === "float") {
    styleName = "cssFloat";
  }
  try {
    let computed = document.defaultView!.getComputedStyle(element, "");
    return element.style.getPropertyValue(styleName) || computed
      ? computed.getPropertyValue(styleName)
      : null;
  } catch (e) {
    return element.style.getPropertyValue(styleName);
  }
}

/**
 * Element.classList.toggle() using `force` parameter, compatible with IE10/11.
 * @param el html element
 * @param token a *single* class name (no spaces)
 * @param active true to add class, false to remove
 */
export const toggleClass = (el: Element, token: string, active: boolean) => {
  el.classList[active ? "add" : "remove"](token);
};

/**
 * Returns the element's top offset relative to the entire document.
 * @param el html element
 */
export const offsetTop = (el: Element) => {
  return window.pageYOffset + el.getBoundingClientRect().top;
};
