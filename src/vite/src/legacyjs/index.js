import App from "./app";
import KoohiiNav from "./revtk/KoohiiNav";

// import EventCache from "@old/ui/eventcache.js";
// import EventDelegator from "@old/ui/eventdelegator.js";
// import EventDispatcher  from "@old/ui/eventdispatchaer.js";
// import AjaxIndicator  from "@old/ui/ajaxindicator.js";
// import AjaxQueue from "@old/ui/ajaxqueue.js";
// import AjaxRequest from "@old/ui/ajaxrequest.js";
// import AjaxPanel from "@old/ui/ajaxpanel.js";
// import AjaxDialog from "@old/ui/ajaxdialog.js";
// import Keyboard from "@old/ui/keyboard.js";
// import Mobile from "@old/ui/mobile.js";
/* FIXME ( OLD DEPENDENCIES, remove after refactor ) */

/* AjaxTable (+ rows-per-page FilterStd) */
/* =require "/widgets/ajaxtable/ajaxtable.js" */
/* =require "/widgets/filterstd/filterstd.js" */

export default {
  init() {
    console.log("legacy-bundle()");

    // desktop navigation
    window.addEventListener("DOMContentLoaded", function () {
      KoohiiNav.init();
    });
  },
};
