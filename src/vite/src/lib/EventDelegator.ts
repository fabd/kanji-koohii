/**
 * EventDelegator
 *
 * "Event delegation is a technique whereby you use a single event handler on
 * a parent element to listen for interactions that affect the parent's descendant
 * elements; because events on the descendant elements will bubble up to the
 * parent, this can be a reliable and extremely efficient mitigation strategy for
 * reducing the number of resource-consuming event handlers you have on any
 * given page." -- YUI2
 *
 *   (new EventDelegator("#AjaxTable"))
 *     .onRoot('click', this.onClickTable.bind(this))
 *     .on('click', '[data-action="add"]', this.onClickAdd.bind(this));
 *
 *
 * Methods:
 *   constructor(element|selector)
 *
 *   on(type(s), selector, callback, scope?)
 *
 *   onRoot(type(s), callback, scope?)
 *
 *   clear()
 *
 *   destroy()
 *
 *
 * Callback signature:
 *
 *   callback(event: Event, matchedEl: Element)
 *
 *     The callback receives the event object, and the current element in the bubble chain.
 *     matchedEl is the element matching the selector that was passed to `on()`. Note this
 *     may *not* be the same as event.target!
 *
 *     When using onRoot(), matchedEl is always the root element. In the onRoot() callback
 *     use `event.target` to get the element that started the event chain.
 *
 *     Return `false` explicitly to stop the event and interrupt the event chain.
 *
 *     Otherwise the event will continue bubbling up to the onRoot() callback if set.
 *     If the onRoot() callback is not set or does not return `false`, then the event
 *     triggers the default behaviour (eg. links, submit form, etc).
 *
 *
 * Features:
 *
 *   // method chaining
 *   const evtDel = (new EventDelegator("#MyComponent"))
 *     .on('click', '[data-action="add"]', this.onClickAdd.bind(this))
 *     .on(...)
 *
 *   // handle multiple events via the root element
 *   evtDel.onRoot(['keyup', 'keydown'], this.onKeyEvent, this)
 *
 *   // remove all event listeners registered on the root element
 *   evtDel.clear()
 *
 */

import Lang from "@lib/lang";

type EDCallback = (event: Event, target: Element) => boolean | void;
type EDListener = {
  type: string;
  selector: string;
  callback: EDCallback;
  scope: any;
};

export default class EventDelegator {
  private rootNode: Element;
  private eventTypes: Set<string>;
  private eventHandler;
  private listeners: EDListener[];
  private rootListener: EDListener | null;

  /**
   *
   * @param target target element as a Node or a query selector
   */
  constructor(target: Element | string) {
    if (Lang.isString(target)) {
      target = document.querySelector(target)!;
    }

    console.assert(
      Lang.isNode(target),
      "EventDelegator() `target` is not a valid node."
    );

    this.rootNode = target;
    this.eventTypes = new Set();
    this.eventHandler = this._handler.bind(this);
    this.listeners = [];
    this.rootListener = null;
  }

  clear() {
    for (const type of this.eventTypes) {
      this.rootNode.removeEventListener(type, this.eventHandler);
    }
    this.eventTypes.clear();
  }

  destroy() {
    this.clear();
  }

  /**
   * Listen to events of given type(s) bubbling up to element matching selector.
   * Types can be a single string or an array, eg. ["click", "keyup"].
   * Callback target is the element matching the selector, it may not be event.target!
   *
   * NOTE: this method can be chained.
   */
  on(
    types: string | string[],
    selector: string,
    callback: EDCallback,
    scope?: any
  ) {
    console.assert(selector.length);
    return this._on(types, selector, callback, scope);
  }

  /**
   * Listen to events of given type(s), bubbling up to the root element.
   * Types can be a single string or an array, eg. ["click", "keyup"].
   * Callback target will always be the root element passed to the constructor.
   *
   * NOTE: this method can be chained.
   */
  onRoot(types: string | string[], callback: EDCallback, scope?: any) {
    return this._on(types, "", callback, scope);
  }

  _on(
    types: string | string[],
    selector: string,
    callback: EDCallback,
    scope: any
  ) {
    if (Lang.isString(types)) {
      types = [types];
    }

    for (const type of types) {
      // register event handler for this type of event if not done yet
      if (!this.eventTypes.has(type)) {
        this.rootNode.addEventListener(type, this.eventHandler);
        this.eventTypes.add(type);
      }

      const listener = {
        type,
        selector,
        callback,
        scope,
      };

      if (selector === "") {
        this.rootListener = listener;
      } else {
        this.listeners.push(listener);
      }
    }

    return this;
  }

  _handler(event: Event) {
    let target: Element | null = event.target as Element;

    while (target && target !== this.rootNode) {
      for (const listener of this.listeners) {
        if (
          target.matches(listener.selector) &&
          event.type === listener.type &&
          this._fire(listener, event, target) === false
        ) {
          event.stopPropagation();
          event.preventDefault();
          return false;
        }
      }

      target = target.parentElement;
    }

    if (target === this.rootNode && this.rootListener) {
      this._fire(this.rootListener, event, this.rootNode);
    }
  }

  _fire(listener: EDListener, event: Event, target: Element): any {
    return listener.callback.apply(listener.scope, [event, target]);
  }
}
