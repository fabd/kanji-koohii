// FIXME: remove with Vite 2.3.x ?
//   https://github.com/vitejs/vite/blob/main/packages/vite/CHANGELOG.md#230-2021-05-11
import "vite/dynamic-import-polyfill";

import LegacyBundle from "@old/index";
LegacyBundle.init();

import App from "@old/app";
import Dom from "@lib/koohii/dom";
import KoohiiAside from "@/components/Aside";

export function init() {
  window.App = App;

  window.Koohii = {
    Dom: Dom,
    Refs: {},
    UX: {},
  };

  window.Koohii.UX = {
    // site-wide mobile navigation
    KoohiiAside,
  };

  window.addEventListener("DOMContentLoaded", () => {
    App.init();
  });

  console.log("@root-bundle");
}
