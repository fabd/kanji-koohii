/**
 * EventCache keeps track of event listeners and makes it easier to remove them all
 * at once.
 *
 * Methods:
 *
 *   addEvent(target, type, callback);
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
    callback: EventListener;
  }[];

  constructor() {
    this.cache = [];
  }

  addEvent<K extends keyof HTMLElementEventMap>(
    target: HTMLElement,
    event: K,
    listener: (e: HTMLElementEventMap[K]) => void
  ) {
    target.addEventListener(event, listener);
    this.cache.push({ target, type: event, callback: listener as EventListener});
  }

  /**
   * Remove all event listeners.
   */
  destroy() {
    while (this.cache.length) {
      const evt = this.cache.pop()!;
      evt.target.removeEventListener(evt.type, evt.callback);
    }
  }
}
