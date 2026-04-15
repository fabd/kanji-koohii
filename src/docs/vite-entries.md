# Vite Entries & data flow between backend and frontend

## Scripts execution

1. **Vite bundles** are included in the document `<head>`. Because they are ESM modules, they are [implicitly deferred](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script#attr-defer)

2. **php outputs data for Vue templating via a global `window.KK` object**. Because `<script>` tags are part of the document, this data is parsed _before_ deferred modules are executed.

3. **deferred ESM modules are executed** after the document has been parsed, but *before* firing `DOMContentLoaded`.

4. **DOMContentLoaded events firing**
     - any `DOMContentLoaded` events set up with `koohii_onload_slot()` in the php templates will execute first since they were parsed as part of the document
     - followed by `DOMContentLoaded` events set up with the `domContentLoaded` helper in Vite entries, in the order entries were included (`entry-common` first, then the page-specific entry)

## Vite entries

Some Vite entries facts:

- a vite entry can be used on different page, therefore a common pattern is to look for a dom element with a particular id, before doing any setup such as instancing a Vue component. Likewise, the presence of an element on the page can be used to identify which page we are on.
- `vite/src/entry-common.ts` is included in ALL pages, and *before* the page-specific entry, so it executes also first

**Example entry that sets up a Vue component**. To simplify this common pattern, a `VueInstance` helper is available. This helper mounts a Vue 3 component to a DOM element with optional props.

In the following example $$ is a lightweight jQuery-like selector, and domContentLoaded is a helper that sets up a `DOMContentLoaded` event listener:

```ts
import VueInstance from "@/lib/helpers/vue-instance";
import SomeComponent from "@/vue/SomeComponent.vue";
import $$, { domContentLoaded } from "@/lib/dom";

// defer initialization until DOM + deferred scripts are ready
domContentLoaded(() => {
  // find element by id, mount Vue component to it
  const mount = $$("#WhereToMount")[0];
  // note how an entry can be used on different pages, so we check if the element is present
  if (mount) {
    VueInstance(SomeComponent, mount);
  }
});
```

Note the VueInstance helper returns an object with an `unmount` method which can be used to unmount the component. Calling this function removes the component from the page DOM, and triggers the unmount lifecycle hooks for all components in this component and its children.

```ts
// mount
const vueInst = VueInstance(SpacedRepetitionForm, mount);
// ... later in the code (for example a dialog component closes), unmount the component
vueInst.unmount();
```

**When an entry mounts multiple components**, group the setup in a class and instantiate it from `domContentLoaded`. This keeps the entry readable and allows shared local state between mounts:

```ts
import VueInstance from "@/lib/helpers/vue-instance";
import $$, { domContentLoaded } from "@/lib/dom";
import { kk_globals_get } from "@/app/root-bundle";
import ComponentA from "@/vue/ComponentA.vue";
import ComponentB from "@/vue/ComponentB.vue";

class MyPageSetup {
  constructor() {
    const elA = $$("#MountA")[0];
    if (elA) {
      VueInstance(ComponentA, elA, kk_globals_get("PROPS_A"));
    }

    const elB = $$("#MountB")[0];
    if (elB) {
      VueInstance(ComponentB, elB);
    }
  }
}

domContentLoaded(() => {
  new MyPageSetup();
});
```

### How Vite entries are handled by the backend

Production build calls `batch/build-prod.sh`, which runs Vite then pre-parses the manifest into `config/vite-build.inc.php`. This include is used by `coreWebResponse::addViteEntries()` to insert the required js/css chunks for a given bundle.

A module/action can specify the bundle to use on any given page by setting the `javascripts` key in the view.yml file (eg. `apps/koohii/modules/study/config/view.yml`):

```yaml
# apps/koohii/modules/study/config/view.yml
indexSuccess:
  # adds the entry to the index action only (eg. /study page)
  javascripts: [src/entry-study.ts]

all:
  # adds the entry to ALL actions in this module (ie. /study/* pages)
  javascripts: [src/entry-study.ts]
```

If the javascript filename is in the pattern `src/entry-(name).ts` then it is recognized as a Vite bundle and the associated css/js dependencies are added to the response.

## Data flow between backend and frontend - the global KK object

The backend PHP code transmits data to the front end through a global window.KK object which is declared in a `<script>` tag.

A common pattern is the PHP action sets props for a Vue component to be mounted on the client side.

**PHP side** — use `kk_globals_put()` anywhere in the backend (typically in actions) to add key/values to the KK object:

```php
kk_globals_put([
  'USER_KANJI_CARDS'      => ReviewsPeer::getUserKanjiCardsJS($userId, $cardsIds),
  'HOMEDASH_PCTBAR_PROPS' => ['value' => $flashcardCount, 'max-value' => $studyMax],
]);
```

The data is serialized to a `<script>` block by `kk_globals_out()` in `app/koohii/templates/layout.php`:

```html
<script>
window.KK || (KK = {});
KK.USER_KANJI_CARDS = [[19968,{"ucs":19968,"box":3,"new":0}], (...more data)];
KK.HOMEDASH_PCTBAR_PROPS = {"value":70,"max-value":2042};
KK.BASE_URL = "http://kanji.koohii.com/";
</script>
```

**JS side** — `kk_globals_get()` and `kk_globals_has()` are exported from `@app/root-bundle` and provide typed access to the KK object:

```ts
import { kk_globals_get } from "src/app/root-bundle";
import KoPctBar from "src/vue/KoPctBar.vue";

// ... page setup logic ...

const mount = $$("#JsHomePctBar")[0];
if (mount) {
  VueInstance(KoPctBar, mount, kk_globals_get("HOMEDASH_PCTBAR_PROPS"));
}
```

When adding new keys to the KK object, they have to be declared in `vite/src/types/globals.d.ts`:

```ts
interface Window {
  KK: {
    // base URL for API requests, is *always* set
    BASE_URL: string;

    // props for the progress bar on the homepage dashboard
    HOMEDASH_PCTBAR_PROPS: Dictionary;

    // ...
  }
}
```

For convenience a default value can be set when reading a key. If the key is not present and default value is not set, it returns undefined:

`kk_globals_get("KEY_NAME", 'defaultValue')`

You can also check for the presence of a key:

`kk_globals_has("KEY_NAME")`

## Window Globals

Note the `KK.BASE_URL` key is always output, it is used mainly by the API to construct absolute urls.

`window.Koohii.Refs` is used as a global property bag to pass references between components. It is a less than ideal solution, and preferably not used by new code.

```ts
// this can then be access globally from any component
window.Koohii.Refs.KanjiReview = new KanjiReview(fcrOptions, props as TKanjiReviewProps);
```

`window.Koohii.UX` holds a reference to a legacy component `AJaxTable` which is used on several pages. This allows to instance this legacy component in any given page without requiring to write an entry to set it up. For example an action template can do this:

```php
<?php koohii_onload_slot(); ?>
  var ajaxTable = new Koohii.UX.AjaxTable('MembersListComponent');
<?php end_slot(); ?>
```

## Onload Slots

For simple scenarios, the backend can also output setup code directly in the template of any action, by using the `koohii_onload_slot()` helper. Note this helper can be used multiple times:

```php
<?php koohii_onload_slot(); ?>
  // initialize a dynamic sortable table
  const ajaxTable = new Koohii.UX.AjaxTable('MembersListComponent');
<?php end_slot(); ?>
```

When the layout is rendered a `<script>` block is generated, with the "onload" code wrapped in a DOMContentLoaded event handler.

```js
<script>
  window.addEventListener('DOMContentLoaded',function(){
  const ajaxTable = new Koohii.UX.AjaxTable('MembersListComponent');
});
</script>
```

## See Also

- docs/front-end-api.md covers the API layer
- docs/symfony-mvc.md covers the PHP side
