/**
 * LEGACY helpers that augmented YUI2 with utilities to create classes.
 *
 *   make()           OOP, returns constructor for a base class
 *   extend()         OOP, returns a constructor for an extended class
 *   ready()          Sets window onload code
 *
 */

/* Copy/Paste board.

import $$, { domGet, px } from "@lib/dom";
import Core from "@old/core";
import AjaxDialog from "@old/ajaxdialog";
import AjaxIndicator  from "@old/ajaxindicator";
import AjaxPanel from "@old/ajaxpanel";
import AjaxQueue from "@old/ajaxqueue";
import AjaxRequest from "@old/ajaxrequest";
import AjaxTable from "@old/ajaxtable";
import Keyboard from "@old/keyboard";
import EventCache from "@old/eventcache";
import EventDelegator from "@old/eventdelegator";
import EventDispatcher  from "@old/eventdispatcher";

*/


export default {
  /**
   * A constructor function to create a new class.
   *
   * Examples:
   *   ```
   *   var Widget = Core.make();
   *   Widget.prototype = { init(), etc. }
   *   ```
   *
   * @param {Object} px   Optional prototype object containing properties and methods
   * @return {Function}   Class constructor that will call init() method when instanced
   */
  make(px) {
    var fn = function () {
      return this.init.apply(this, arguments);
    };

    // optional: set prototype for the new class
    if (px) {
      fn.prototype = px;
    }

    return fn;
  },
};

export default Core;
