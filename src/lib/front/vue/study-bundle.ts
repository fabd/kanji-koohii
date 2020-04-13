/**
 * SITE-WIDE (aka 'STUDY') BUNDLE
 *
 *   This bundle is used on all pages, except landing (home) &
 *   fullscreen flashcard review.
 *
 *   Includes:
 *   - root-bundle: Vue, mobile navigation, globals
 *   - legacy study & manage flashcard stylesheets
 *   - components instanced externally by Study & misc. other pages
 *
 */

// import legacy stylesheets
import "@web/koohii/main.build.scss";
import "@web/koohii/study-base.build.scss";
import "@web/koohii/manage.build.scss";

import * as RootBundle from "@lib/helpers/root-bundle";
RootBundle.init();

// components instanced by external code
import LeitnerChart from "@components/LeitnerChart.vue";
import KoohiiDictList from "@components/KoohiiDictList.vue";
import KoohiiEditStory from "@components/KoohiiEditStory.vue";

// declare some globals to be available for legacy Javascript (non-Vue build)
import { KoohiiAPI } from "@lib/KoohiiAPI";

// for legacy code upgrade path
window.Koohii.API = KoohiiAPI;

window.Koohii.UX = {
  ...window.Koohii.UX,
  LeitnerChart,
  KoohiiDictList,
  KoohiiEditStory,
};

console.log("@study-bundle");
