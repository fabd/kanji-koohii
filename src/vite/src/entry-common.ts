/** 
 * entry-common is a base bundle included in all pages of the site
 * (cf. coreWebResponse.php)
 * 
 */

// common stylesheets we want across the site
import "@css/main.build.scss";

console.log("@entry-common");

// init the site-wide navigation (desktop & mobile)
import rootBundleInit from "@app/root-bundle";
rootBundleInit();
