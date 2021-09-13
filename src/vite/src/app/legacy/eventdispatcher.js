/**
 * EventDispatcher implements the observer design pattern.
 *
 * Methods:
 *
 *   connect(name, fn [, scope])           Subscribe to a custom event.
 *   disconnect(name[, fn])                Unsubscribe (ie. disconnect the listener).
 *   notify(name [, args...])              Notify all listeners about a custom event.
 *   hasListeners(name)                    Check whether there are any listeners to a custom event.
 *
 * @see     http://developer.apple.com/documentation/Cocoa/Conceptual/Notifications/index.html Apple's Cocoa framework
 *
 */
// @ts-check

import Lang from "@lib/lang";

/** @typedef {{fn: Function, scope: any}} ListenerInfo */

export default class EventDispatcher {
  /** @type {Dictionary<ListenerInfo[]>} */
  listeners = {};

  constructor() {
    this.listeners = {};
  }

  destroy() {
    this.listeners = {};
  }

  /**
   * Connects a listener to a given event name.
   *
   * @param {string}    name     The type of event (the event's name)
   * @param {Function}  fn       A javascript callable
   * @param {Object=}    context  Context (this) for the event. Default value: the window object.
   */
  connect(name, fn, context) {
    if (!this.listeners[name]) {
      this.listeners[name] = [];
    }

    this.listeners[name].push({
      fn: fn,
      scope: context || window,
    });
  }

  /**
   * Disconnects a listener, or all listeners, for an event.
   *
   * If fn is not specified, then all listeners for this event are unsubscribed.
   *
   * @param {string}    name   An event name
   * @param {Function=}  fn     A javascript callable (optional)
   *
   * @return {number|null}    Number of listeners unsubscribed, or null the listener is not found
   */
  disconnect(name, fn) {
    if (!this.listeners[name]) {
      return null;
    }

    // if listener is undefined, delete all listeners
    const deleteAll = !fn;

    let callables = this.listeners[name];
    let l = callables.length;

    for (let i = l - 1; i >= 0; i--) {
      let s = callables[i];
      if (deleteAll || s.fn === fn) {
        callables.splice(i, 1);
      }
    }

    return l;
  }

  /**
   * Notifies all listeners of a given event.
   *
   * @param {string} name  An event name
   * @param {...any=} params  An arbitrary set of parameters to pass to the handler.
   *
   * @return {boolean|null} false  False if one of the subscribers returned false, true otherwise
   */
  notify(name, params) {
    var args = Array.prototype.slice.call(arguments, 1),
      callables,
      i,
      ret,
      subscriber;

    callables = this.listeners[name] ?? [];

    console.assert(
      args.length !== 1 || !Lang.isArray(args[0]),
      "EventDispatcher::notify()  using obsolete signature?"
    );

    if (!callables.length) {
      return null;
    }

    for (i = 0; i < callables.length; i++) {
      subscriber = callables[i];
      ret = subscriber.fn.apply(subscriber.scope, args.length ? args : []);
      if (false === ret) {
        break;
      }
    }

    return ret !== false;
  }

  /**
   * Returns true if the given event name has some listeners.
   *
   * @param {string} name    An event name
   *
   * @return {boolean} true if some listeners are connected, false otherwise
   */
  hasListeners(name) {
    if (!this.listeners[name]) {
      return false;
    }
    return this.listeners[name].length > 0;
  }
}
