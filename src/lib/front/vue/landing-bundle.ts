/**
 * LANDING PAGE BUNDLE
 * 
 *   Includes:
 *   - root-bundle: Vue, mobile navigation, globals
 *   - the landing page stylesheet
 *
 */

// import "landing page only" stylesheet
import "@web/koohii/home.build.scss";

import * as RootBundle from "@lib/helpers/root-bundle";
RootBundle.init();

console.log("@landing-bundle");
