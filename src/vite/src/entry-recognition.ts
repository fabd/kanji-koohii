import { domGetById, domContentLoaded } from "@lib/dom";
import VueInstance from "@lib/helpers/vue-instance";

import "@/assets/css/RecognitionApp.css";

import RecognitionApp from "@/vue/recognition/RecognitionApp.vue";

class RecognitionPage {
  constructor() {
    const elMount = domGetById("JsRecognitionApp")!;
    elMount && VueInstance(RecognitionApp, elMount);
  }
}

domContentLoaded(() => {
  console.log("@entry-recognition");
  new RecognitionPage();
});
