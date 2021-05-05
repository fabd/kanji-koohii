/**
 * LEGACY bundle with YUI2 library.
 *
 */

console.log('entry legacy...');

var Koohii = {
  Dom: null,
};

import App from "@old/app.js";

// import EventCache from "@old/ui/eventcache.js";
// import EventDelegator from "@old/ui/eventdelegator.js";
// import EventDispatcher  from "@old/ui/eventdispatcher.js";
// import AjaxIndicator  from "@old/ui/ajaxindicator.js";
// import AjaxQueue from "@old/ui/ajaxqueue.js";
// import AjaxRequest from "@old/ui/ajaxrequest.js";
// import AjaxPanel from "@old/ui/ajaxpanel.js";
// import AjaxDialog from "@old/ui/ajaxdialog.js";
// import Keyboard from "@old/ui/keyboard.js";
// import Mobile from "@old/ui/mobile.js";

/* AjaxTable (+ rows-per-page FilterStd) */
/* =require "/widgets/ajaxtable/ajaxtable.js" */
/* =require "/widgets/filterstd/filterstd.js" */

/* Dependencies for the custom tooltip on the "reading" page */
/* =require from "%YUI2%" */
/* =require "/container/container-min.js" */

/* KoohiiNav */
/* =require from "%WEB%" */
/* =require "/revtk/components/KoohiiNav.js" */

var Y = YAHOO,
  $$ = Koohii.Dom,
  Dom = Y.util.Dom,
  Event = Y.util.Event;

/**
 * Custom Tooltip using YUI 2.
 *
 * Goals:
 * - Ability to display on regular mouse click of the context element, for
 * touch devices.
 *
 * Methods:
 *   init()         Constructor.
 *   show()         Forcibly display the overlay.
 *   hide()         Forcibly hide the overlay.
 *   isVisible()
 *   destroy()
 *
 * Constructor options:
 *   id             Id to apply to the tooltip container. Markup will be
 *                      <div id="..." yui-stuff ><div class="bd">content</div></div>
 *   context        Element to align tooltip to.
 *   content        Content for the tooltip, can use HTML.
 *
 * @author  Fabrice Denis
 */
App.Ui.CustomTooltip = Core.make();

App.Ui.CustomTooltip.prototype = {
  init: function (options) {
    console.log("CustomTooltip::init()");

    // set defaults
    this.options = {
      ...{
        id: "CustomTooltip",
      },
      ...options,
    };

    // create container
    var div = document.createElement("div");
    div.id = options.id;

    this.overlay = new Y.widget.Overlay(div, {
      context: [options.context, "bl", "tl"],
      visible: false,
    });

    this.overlay.setBody(options.content);
    this.overlay.render(document.body);
  },

  destroy: function () {
    console.log("CustomTooltip::destroy()");
    this.overlay.destroy();
    this.overlay = null;
  },

  show: function () {
    console.log("CustomTooltip::show()");
    console.assert(!!this.overlay);

    // compute width and center horizontally
    //$$(div).css({ display:"block", visibility:"hidden", position:"absolute" });
    //Core.Ui.Helper.insertTop(div);

    var el = this.overlay.body,
      width = el.offsetWidth > 50 ? el.offsetWidth : 50,
      ofsLeft = -Math.floor(width / 2) + 5;
    this.overlay.align("bl", "tl", [ofsLeft, 0]);
    this.overlay.show();
  },

  hide: function () {
    console.log("CustomTooltip::hide()");
    console.assert(!!this.overlay);
    this.overlay.hide();
  },

  isVisible: function () {
    return this.overlay.cfg.getProperty("visible");
  },
};

console.log("entry-legacy()");
