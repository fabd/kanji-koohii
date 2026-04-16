// set up for the `/ux` live docs/testing ground

import { domGetById, domContentLoaded } from "@/lib/dom";
import VueInstance from "@/lib/helpers/vue-instance";

import "./app/ux/docs.css";

import UxIndex from "@/app/ux/ux-index.vue";

domContentLoaded(() => {
  console.log("@entry-styleguide");
  const elMount = domGetById("JsStyleguideApp")!;
  if (elMount) {
    VueInstance(UxIndex, elMount);
  }
});
