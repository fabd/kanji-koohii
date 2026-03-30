/**
 * EventDispatcher implements the observer design pattern.
 *
 * Methods:
 *
 *   connect(name, fn [, scope])
 *   disconnect(name[, fn])
 *   notify(name [, args...])
 *   hasListeners(name)
 *
 */

export type ListenerFn = (...args: any[]) => any;

type ListenerInfo = {
  fn: ListenerFn;
  scope: object | undefined;
};

export default class EventDispatcher {
  private listeners: Record<string, ListenerInfo[]>;

  constructor() {
    this.listeners = {};
  }

  destroy() {
    this.listeners = {};
  }

  /**
   * Subscribe to a custom event.
   *
   * @param name     The type of event (the event's name)
   * @param fn       A javascript callable
   * @param scope    Context (this) for the event, optional.
   */
  connect(name: string, fn: ListenerFn, scope?: object) {
    if (!this.listeners[name]) {
      this.listeners[name] = [];
    }

    this.listeners[name].push({ fn, scope });
  }

  /**
   * Unsubscribe listener from an event.
   *
   * If fn is not specified, then all listeners for this event are unsubscribed.
   *
   * @param name   An event name
   * @param fn     A javascript callable (optional)
   *
   */
  disconnect(name: string, fn?: ListenerFn): void {
    if (!this.listeners[name]) {
      return;
    }

    const deleteAll = !fn;
    const listeners = this.listeners[name];

    this.listeners[name] = deleteAll
      ? []
      : listeners.filter((l) => l.fn !== fn);
  }

  /**
   * Notifies all listeners of a given event.
   *
   * @param name   An event name.
   * @param args   An arbitrary set of arguments to pass to the listener.
   *
   * @return  False if one of the subscribers returned false, or there are no listeners for this event, otherwise true.
   */
  notify(name: string, ...args: any[]): boolean {
    const callables = this.listeners[name] ?? [];

    if (!callables.length) {
      return false;
    }

    let result = true;
    for (let i = 0; i < callables.length; i++) {
      const subscriber = callables[i]!;
      const ret = subscriber.fn.apply(subscriber.scope, args);
      result = result && ret !== false;
    }

    return result;
  }

  /**
   * Returns true if the given event name has some listeners.
   *
   * @param name    An event name
   *
   * @return true if some listeners are connected, false otherwise
   */
  hasListeners(name: string): boolean {
    return (this.listeners[name]?.length ?? 0) > 0;
  }
}
