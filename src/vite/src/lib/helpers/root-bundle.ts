/**
 * Common dependencies for the multi-page entries.
 */

// FIXME: remove with Vite 2.3.x ?
//   https://github.com/vitejs/vite/blob/main/packages/vite/CHANGELOG.md#230-2021-05-11
import "vite/dynamic-import-polyfill";

import LegacyBundle from "@old/index";
LegacyBundle.init();

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
