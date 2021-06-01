type Dictionary<T> = { [key: string]: T };

// --------------------------------------------------------------------
// legacy component definitions; for .js files imported in .ts
// --------------------------------------------------------------------
// ajaxdialog.js
interface IAjaxDialog {
  new (srcMarkup: string | null, options?: Dictionary): this;
  getAjaxPanel(): any;
  getBody(): Element;
  on(className: string, fn: Function, scope?: any): void;
  destroy(): void;
  show(): void;
}

// eventcache.js
interface IEventCache {
  new (sId?: string): this;
  addEvent(target: Element, type: string, listener: Function);
  addEvents(target: Element, eventTypes: string[], fn: Function);
  destroy(): void;
}
