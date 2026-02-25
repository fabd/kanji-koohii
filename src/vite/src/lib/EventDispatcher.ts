/**
 * EventDispatcher implements the observer design pattern.
 *
 * Methods:
 *
 *   connect(name, fn [, scope])  Subscribe to a custom event.
 *   disconnect(name[, fn])       Unsubscribe fn, or all listeners from the event.
 *   notify(name [, args...])     Notify all listeners about a custom event.
 *   hasListeners(name)           Check whether there are any listeners to a custom event.
 *
 */

export type ListenerFn = (...args: any[]) => any;

type ListenerInfo = {
  fn: ListenerFn;
  scope: any;
};

export default class EventDispatcher {
  listeners: Dictionary<ListenerInfo[]> = {};

  constructor() {
    this.listeners = {};
  }

  destroy() {
    this.listeners = {};
  }

  /**
   * Connects a listener to a given event name.
   *
   * @param name     The type of event (the event's name)
   * @param fn       A javascript callable
   * @param context  Context (this) for the event. Default value: the window object.
   */
  connect(name: string, fn: ListenerFn, context?: any) {
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
   * @param name   An event name
   * @param fn     A javascript callable (optional)
   *
   * @return Number of listeners unsubscribed, or null the listener is not found
   */
  disconnect(name: string, fn?: ListenerFn): number | null {
    if (!this.listeners[name]) {
      return null;
    }

    // if listener is undefined, delete all listeners
    const deleteAll = !fn;

    const callables = this.listeners[name];
    const l = callables.length;
    for (let i = 0; i < l; i++) {
      const listener = callables[i]!;
      if (deleteAll || listener.fn === fn) {
        callables.splice(i, 1);
      }
    }

    return l;
  }

  /**
   * Notifies all listeners of a given event.
   *
   * @param name   An event name.
   * @param args   An arbitrary set of arguments to pass to the listener.
   *
   * @return  False if one of the subscribers returned false, true otherwise
   */
  notify(name: string, ...args: any[]): boolean | null {
    const callables = this.listeners[name] ?? [];

    if (!callables.length) {
      return null;
    }

    let ret;
    for (let i = 0; i < callables.length; i++) {
      const subscriber = callables[i]!;
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
   * @param name    An event name
   *
   * @return true if some listeners are connected, false otherwise
   */
  hasListeners(name: string): boolean {
    return (this.listeners[name] && this.listeners[name].length > 0) || false;
  }
}
