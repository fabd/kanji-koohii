import Dom, { domContentLoaded } from "@lib/dom";
import AjaxTable from "@old/ajaxtable";
import EventDelegator from "@old/eventdelegator";
import KoohiiAside from "@/vue/Aside";
import KoohiiNav from "@old/components/KoohiiNav";

let bodyED: IEventDelegator | null = null;

/**
 * Returns an EventDelegator instance for click events on the page body.
 */
export function getBodyED(): IEventDelegator {
  return bodyED
    ? bodyED
    : (bodyED = new (EventDelegator as IEventDelegator)(
        document.body,
        "click"
      ));
}

/**
 * Focus an element on page load, if the required classes are present:
 *
 *   - if a form with class `JsFocusOnLoadError` has an error, focus that field
 *   - otherwise if an element has class `JsFocusOnLoadInput`, focus that input
 */
function focusOnLoad() {
  const $$ = Dom;
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

export default function() {
  console.log("... rootBundleInit()");

  window.Koohii = {
    Dom: Dom,
    Refs: {},
    UX: {
      // legacy AjaxTable component instanced in pages like Members List
      AjaxTable,
      // site-wide mobile navigation
      KoohiiAside,
    },
  };

  domContentLoaded(() => {
    // init the site-wide desktop navigation
    KoohiiNav.init();

    focusOnLoad();
  });
}
