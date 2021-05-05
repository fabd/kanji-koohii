
/**
 * LANDING PAGE BUNDLE
 *
 *   Includes:
 *   - root-bundle: Vue, mobile navigation, globals
 *   - the landing page stylesheet
 *
 */

// console.log("@landing-bundle 1");

// import "landing page only" stylesheet
import "@css/home.scss";


import * as RootBundle from "@lib/helpers/root-bundle";
RootBundle.init();

console.log("@entry landing");
