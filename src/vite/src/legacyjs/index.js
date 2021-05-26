import App from "@old/app";
import KoohiiNav from "@old/revtk/KoohiiNav";

/*
import $$, { domGet, px } from "@lib/koohii/dom";

import Core from "@old/core";

import AjaxDialog from "@old/ui/ajaxdialog";
import AjaxIndicator  from "@old/ui/ajaxindicator";
import AjaxPanel from "@old/ui/ajaxpanel";
import AjaxQueue from "@old/ui/ajaxqueue";
import AjaxRequest from "@old/ui/ajaxrequest";
import AjaxTable from "@old/ui/ajaxtable";
import Keyboard from "@old/ui/keyboard";
import EventCache from "@old/ui/eventcache";
import EventDelegator from "@old/ui/eventdelegator";
import EventDispatcher  from "@old/ui/eventdispatcher";

*/

export default {
  init() {
    console.log("legacy-bundle()");

    // globals accessed via php templates
    window.App = App;

    // desktop navigation
    window.addEventListener("DOMContentLoaded", function () {
      KoohiiNav.init();
    });
  },
};
