/**
 * EventCache keeps track of event listeners and makes it easier to remove them all
 * at once.
 *
 * Methods:
 *
 *   addEvent(target, type, callback);
 *   addEvents(target, types, callback);
 *   destroy()
 *
 * Examples:
 *   this.evtCache = new EventCache();
 *   this.evtCache.addEvent(document.body, 'click', this.onClick.bind(this));
 *
 */

export default class EventCache {
  private cache: {
    target: Node;
    type: string;
    callback: EventListenerOrEventListenerObject;
  }[];

  constructor() {
    this.cache = [];
  }

  addEvent(
    target: Node,
    type: string,
    callback: EventListenerOrEventListenerObject
  ) {
    target.addEventListener(type, callback);
    this.cache.push({ target, type, callback });
  }

  /**
   * Bind multiple events to one listener.
   *
   */
  addEvents(
    target: Node,
    types: string[],
    callback: EventListenerOrEventListenerObject
  ) {
    for (const type of types) {
      this.addEvent(target, type, callback);
    }
  }

  destroy() {
    while (this.cache.length) {
      const evt = this.cache.pop()!;
      evt.target.removeEventListener(evt.type, evt.callback);
    }
  }
}
