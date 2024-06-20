import $$, { domContentLoaded } from "@lib/dom";
import AjaxTable from "@old/ajaxtable";
import EventDelegator from "@old/eventdelegator";
import KoohiiAside from "@/vue/Aside";
import KoohiiNav from "@old/components/KoohiiNav";

let bodyED: EventDelegator | null = null;

/**
 * Returns an EventDelegator instance for click events on the page body.
 */
export function getBodyED(): EventDelegator {
  return bodyED ? bodyED : (bodyED = new EventDelegator(document.body, "click"));
}

/**
 * Focus an element on page load, if the required classes are present:
 *
 *   - if a form with class `JsFocusOnLoadError` has an error, focus that field
 *   - otherwise if an element has class `JsFocusOnLoadInput`, focus that input
 */
function focusOnLoad() {
  let elHasError;

  const elForm = $$(".JsFocusOnLoadError")[0];
  if (elForm) {
    elHasError = $$<HTMLElement>(".has-error", elForm)[0];
    elHasError && elHasError.focus();
  }

  let elFocus;
  if (!elHasError && (elFocus = $$<HTMLElement>(".JsFocusOnLoadInput")[0])) {
    elFocus.focus();
  }
}

const kkGlobalsHas = (name: string) => window.KK.hasOwnProperty(name);

/**
 * Helper makes it easier to find code where values are shared between php/js.
 *
 * Cf. `kk_globals_put()` on the php side.
 *
 * @param name key (make sure to declare them in globals.d.ts)
 */
export function kk_globals_get<K extends keyof Window["KK"]>(name: K, defaultVal?: any): Window["KK"][K] {
  console.assert(!!window.KK);
  console.assert(
    typeof defaultVal !== "undefined" || kkGlobalsHas(name),
    `Global KK['${name}'] is not set, and no default value`
  );
  return kkGlobalsHas(name) ? window.KK[name] : defaultVal;
}

export function kk_globals_has(name: keyof Window["KK"]): boolean {
  return kkGlobalsHas(name);
}

export default function () {
  console.log("@root-bundle");

  window.Koohii = {
    Refs: {},
    UX: {
      // legacy AjaxTable component instanced in pages like Members List
      AjaxTable,
    },
  };

  domContentLoaded(() => {
    // init site-wide mobile navigation
    let $elAside = $$("#k-slide-nav-btn");
    if ($elAside.el()) {
      $elAside.on("click", () => {
        KoohiiAside.open({ navOptionsMenu: kk_globals_get("MBL_NAV_DATA") });
      });
    }

    // init the site-wide desktop navigation
    KoohiiNav.init();

    focusOnLoad();
  });
}
