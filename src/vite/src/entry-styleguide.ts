// set up for the `/ux` live docs/testing ground

import { domGetById, domContentLoaded } from "@lib/dom";
import VueInstance from "@lib/helpers/vue-instance";

import "@/assets/css/pages/styleguide/docs.scss";

import UxIndex from "@/vue/styleguide/ux-index.vue";

domContentLoaded(() => {
  console.log("@entry-styleguide");
  const elMount = domGetById("JsStyleguideApp")!;
  elMount && VueInstance(UxIndex, elMount);
});
