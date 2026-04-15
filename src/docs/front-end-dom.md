# Front-End DOM

## Overview

DOM manipulation and event handling in this app uses a small set of custom utilities. Do **not** use jQuery or other DOM libraries — these helpers cover all common cases.

All utilities live under `vite/src/lib/`. The main ones:

| File | Purpose |
| ---- | ------- |
| `lib/dom.ts` | Core DOM query & manipulation (`$$`, named helpers) |
| `lib/EventDelegator.ts` | Efficient event delegation on a parent element |
| `lib/EventCache.ts` | Track event listeners for bulk cleanup |
| `lib/EventDispatcher.ts` | Custom observer/pub-sub for component-level events |
| `lib/EventBus.ts` | Global singleton `EventDispatcher` |
| `lib/helpers/vue-instance.ts` | Mount / unmount a Vue 3 component |
| `app/root-bundle.ts` | `getBodyED()`, `kk_globals_get()`, `kk_globals_has()` |

---

## `$$` — DOM Query & Manipulation

`$$` is the default export from `lib/dom.ts`. It is a lightweight jQuery-style factory that wraps elements in a `DomJS` instance.

```ts
import $$ from "@/lib/dom";

// Query by CSS selector (returns DomJS wrapping all matches)
const $box = $$(".box");

// Optional context element (searches within it)
const $items = $$("li", someListElement);

// Wrap an existing element or window
$$(myElement).css("display", "none");
$$(window).on("resize", onResize);
```

Convention: use a `$` prefix for `DomJS` variables to distinguish them from raw element references.

### Getting the element

```ts
const el = $box.el();      // first element (default), or null
const el = $box.el(1);     // second element
const el = $box[0];        // array-like access
```

Use `.el()` when you need the actual `HTMLElement` reference. Check its truthiness to see if the element exists:

```ts
if ($$(".optional").el()) {
  // element is in the DOM
}
```

### Chaining

One method may be chained directly after the constructor:

```ts
$$(".banner").css("display", "block");
```

For multiple operations, assign to a variable first:

```ts
const $btn = $$("#submit-btn");
$btn.css("opacity", "0.5");
$btn.on("click", handleClick);
```

### `DomJS` Methods

| Method | Description |
| ------ | ----------- |
| `el(i?)` | Return the element at index `i` (default `0`) |
| `down(selector)` | Query descendants — equivalent to jQuery `find()`, chainable |
| `each((el, index) => void)` | Iterate; return `false` to break early |
| `on(type \| type[], fn)` | Attach event listener(s) to the first element |
| `off(type \| type[] \| fn)` | Detach listeners by type(s) or by listener reference |
| `once(type, fn)` | Attach a listener that fires once then removes itself |
| `css(prop)` | Get inline style value |
| `css(prop, value)` | Set one inline style |
| `css({ prop: value })` | Set multiple inline styles |
| `display(show?)` | `true` (default) clears `display`; `false` sets `display: none` |
| `toArray()` | Return elements as a plain array |

### Named Helpers from `dom.ts`

Import individually as needed:

```ts
import $$, {
  domGetById,
  domContentLoaded,
  hasClass,
  stopEvent,
  getStyle,
  domGetXY,
  insertAfter,
  insertBefore,
  applyCssTransition,
  px,
} from "@/lib/dom";
```

| Helper | Signature | Description |
| ------ | --------- | ----------- |
| `domGetById(id)` | `(id: string \| EL): EL \| null` | Get element by id (no `#` needed) or pass-through an element reference |
| `domContentLoaded(fn)` | `(fn: EventListener): void` | Shorthand for `window.addEventListener("DOMContentLoaded", fn)` |
| `hasClass(el, token)` | `(el: Element, token: string): boolean` | Alias for `el.classList.contains(token)` |
| `stopEvent(evt)` | `(evt: Event): void` | `stopPropagation()` + `preventDefault()` |
| `getStyle(el, prop)` | `(el: HTMLElement, prop: string): string \| null` | Computed style value; `prop` must be camelCase |
| `domGetXY(el)` | `(el: HTMLElement): { top, left }` | Page coordinates of an element |
| `insertAfter(newNode, refNode)` | `(newNode: Element, refNode: Element): Element \| null` | Insert as next sibling |
| `insertBefore(newNode, refNode)` | `(newNode: Element, refNode: Element): Element \| null` | Insert as previous sibling |
| `applyCssTransition(el, className)` | `(el: HTMLElement, className: string): void` | Add `className-from` then `className-active` (Vue-style transition) |
| `px(n)` | `(n: number): string` | Format number as CSS px value, e.g. `px(45)` → `"45px"` |

**Examples:**

```ts
// Wait for DOM ready
domContentLoaded(() => { /* init */ });

// Get element by id (no "#")
const el = domGetById("JsEditStoryInst");

// Stop a click from bubbling / following a link
$$(".cancel-btn").on("click", (e) => stopEvent(e));

// Get computed height
const h = getStyle(el, "height"); // camelCase

// CSS transition: define .fade-from and .fade-active in CSS
applyCssTransition(el, "fade");
```

---

## `EventDelegator` — Event Delegation

Use `EventDelegator` when you need to handle events on dynamic child elements. A single listener on the parent catches all events that bubble up.

```ts
import EventDelegator from "@/lib/EventDelegator";
```

### Constructor

```ts
new EventDelegator(target: Element | string)
```

`target` is the root element (or a CSS selector string for it).

### Methods

| Method | Description |
| ------ | ----------- |
| `on(types, selector, callback, scope?)` | Listen for `types` bubbling up to elements matching `selector` |
| `onRoot(types, callback, scope?)` | Listen for `types` bubbling to the root element itself |
| `clear()` | Remove all event listeners |
| `destroy()` | Alias for `clear()` |

Methods return `this` for chaining.

### Callback signature

```ts
(event: Event, target: Element) => boolean | void
```

- `target` is the matched element (not necessarily `event.target`)
- For `onRoot()`, `target` is always the root element; use `event.target` to get the originating element
- Return `false` explicitly to call `stopPropagation()` + `preventDefault()`

### Examples

```ts
// Basic delegation with chaining
new EventDelegator("#my-list")
  .on("click", '[data-action="delete"]', this.onDelete.bind(this))
  .on("click", '[data-action="edit"]',   this.onEdit.bind(this));

// Listen on the root + multiple event types
const evtDel = new EventDelegator(this.rootEl);
evtDel
  .onRoot(["keyup", "keydown"], this.onKey, this)
  .on("click", ".item", this.onItemClick, this);

// Cleanup
evtDel.destroy();
```

### Page-wide delegator (`getBodyED`)

A shared `EventDelegator` on `document.body` is available for page-level interactions — avoids creating multiple root-level listeners.

```ts
import { getBodyED } from "@/app/root-bundle";

getBodyED().on("click", "[data-dialog]", this.onOpenDialog, this);
```

---

## `EventCache` — Listener Cleanup

Use `EventCache` when you need to track several `addEventListener` calls and remove them all at once (e.g., on component teardown).

```ts
import EventCache from "@/lib/EventCache";
```

```ts
this.evtCache = new EventCache();
this.evtCache.addEvent(document.body, "click",   this.onClick.bind(this));
this.evtCache.addEvent(window,        "resize",  this.onResize.bind(this));

// Later (cleanup)
this.evtCache.destroy();
```

| Method | Description |
| ------ | ----------- |
| `addEvent(target, type, listener)` | Track and attach a typed event listener |
| `destroy()` | Remove all tracked listeners |

---

## `EventDispatcher` — Observer / Pub-Sub

Use `EventDispatcher` for component-level custom events where multiple parts of the code need to react to the same named events.

```ts
import EventDispatcher from "@/lib/EventDispatcher";
```

```ts
const dispatcher = new EventDispatcher();

// Subscribe
dispatcher.connect("card.updated", this.onCardUpdated, this);

// Publish (extra arguments are forwarded to all listeners)
dispatcher.notify("card.updated", cardId, data);

// Unsubscribe specific listener
dispatcher.disconnect("card.updated", this.onCardUpdated);

// Unsubscribe all listeners for an event
dispatcher.disconnect("card.updated");

// Cleanup all
dispatcher.destroy();
```

| Method | Description |
| ------ | ----------- |
| `connect(name, fn, scope?)` | Subscribe to a named event |
| `disconnect(name, fn?)` | Unsubscribe; removes all listeners if `fn` omitted |
| `notify(name, ...args)` | Fire all listeners; returns `false` if any returned `false` or there are no listeners |
| `hasListeners(name)` | Returns `true` if the event has at least one subscriber |
| `destroy()` | Clear all listeners |

---

## `EventBus` — Global Event Bus

`EventBus` is a singleton `EventDispatcher` shared across the entire page. Use it to communicate between decoupled modules, or between Vue components and page-level code.

```ts
import eventBus from "@/lib/EventBus";

// Subscribe (typically in a component's created/mounted hook)
eventBus.connect("kk.flashcard.deleted", this.onDeleted, this);

// Publish
eventBus.notify("kk.flashcard.deleted", cardId);

// Unsubscribe (in beforeUnmount or destroy)
eventBus.disconnect("kk.flashcard.deleted", this.onDeleted);
```

Use descriptive, namespaced event names (e.g. `kk.flashcard.deleted`) to avoid collisions.

---

## `VueInstance` — Mounting Vue Components

Use `VueInstance` to mount a Vue 3 component into a DOM element from non-Vue code (e.g. inside a Vite entry file or a legacy class).

```ts
import VueInstance from "@/lib/helpers/vue-instance";
import MyComponent from "@/vue/MyComponent.vue";
```

```ts
// Mount as a child of #app-mount
const { vm, unmount } = VueInstance(
  MyComponent,
  "#app-mount",
  { propA: value, propB: value }
);

// Later, unmount and clean up
unmount();
```

| Parameter | Type | Description |
| --------- | ---- | ----------- |
| `component` | `Component` | The Vue component definition |
| `mount` | `string \| Element` | Target element or CSS selector |
| `props` | `object` (optional) | Props passed to the component |
| `replace` | `boolean` (default `false`) | If `true`, replace the mount element; if `false`, append inside it |

Returns `{ vm, unmount }`:
- `vm` — the Vue component instance (typed)
- `unmount()` — calls `app.unmount()` to tear down the component

---

## `kk_globals_get` — PHP-to-JS Data

Server-side PHP can inject data into `window.KK` via `kk_globals_put()`. Read these values with the typed helper:

```ts
import { kk_globals_get, kk_globals_has } from "@/app/root-bundle";

// Get a required global (asserts it exists)
const baseUrl = kk_globals_get("BASE_URL");

// Get with a fallback default
const mode = kk_globals_get("REVIEW_MODE", "default");

// Check before accessing
if (kk_globals_has("STUDY_FLASHCARD")) {
  const data = kk_globals_get("STUDY_FLASHCARD");
}
```

Keys must be declared in `vite/src/types/globals.d.ts`. See **docs/vite-entries.md** for how to pass data from PHP to the frontend.

---

## Choosing the Right Tool

| Situation | Use |
| --------- | --- |
| Query / style / show/hide an element | `$$` from `lib/dom.ts` |
| Get element by id string | `domGetById` from `lib/dom.ts` |
| Wait for DOM ready | `domContentLoaded` from `lib/dom.ts` |
| Handle events on dynamically added child elements | `EventDelegator` |
| Handle page-level click events (one shared listener) | `getBodyED()` from `app/root-bundle` |
| Track and bulk-remove multiple `addEventListener` calls | `EventCache` |
| Custom events within a class / component hierarchy | `EventDispatcher` (own instance) |
| Cross-module / cross-component communication | `EventBus` (global singleton) |
| Mount a Vue component from non-Vue code | `VueInstance` |
| Read PHP-injected page data | `kk_globals_get` / `kk_globals_has` |
