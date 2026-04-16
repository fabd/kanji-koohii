/**
 * LANDING PAGE BUNDLE
 *
 *   Includes:
 *   - root-bundle: Vue, mobile navigation, globals
 *   - the landing page stylesheet
 *
 */

// stylesheets
import "./app/landing/home.build.css";

import rootBundleInit from "@/app/common/root-bundle";
rootBundleInit();

console.log("@entry-landing");
