/**
 * Common dependencies for the multi-page entries.
 */

// not sure where this goes, à priori not being used
import "vite/dynamic-import-polyfill";

// include site-wide mobile navigation in the root bundle (for the landing page)
import KoohiiAside from "@/components/Aside";

// export utilities to the legacy front end
import Dom from "@lib/koohii/dom";
import Lang from "@lib/core/lang";
import { Inst as TronFactory } from "@lib/koohii/tron";
import VueInstance from "@lib/helpers/vue-instance";

export function init() {
  const koohiiGlobals: KoohiiGlobals = {
    Dom: Dom,
    TRON: TronFactory,
    Refs: {},
    Util: {
      Lang: Lang,
    },
    UX: {},
  };

  window.Koohii = koohiiGlobals;

  // references for instancing components
  window.Koohii.UX = {
    KoohiiAside,
  };

  console.log("@root-bundle");
}
