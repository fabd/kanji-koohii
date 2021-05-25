/**
 * (old) GoogleAnalytics style radio "switch".
 * 
 * FIXME >>>  convert to a simple Vue component? (or use element-ui?)
 *
 * Setup:
 *   Each A tag within the container must have class JSFilterStd and a unique identifier
 *   in the form "uiFilterStd-xxx" where "xxx" is passed to the listener.
 *
 * Events:
 *   onSwitch(id)     Fires AFTER the tab is active, id is the identifier from the classname.
 *
 * Code:
 *   filt = new FilterStd('my-filter', { onSwitch: this.callback.bind(this) });
 *
 * @author   Fabrice Denis
 */

import Core from "@old/core";
import EventDelegator from "@old/ui/eventdelegator";
import EventDispatcher from "@old/ui/eventdispatcher";

let FilterStd = Core.make();

/**
 * Class name set on the active tab.
 */
const ACTIVE = "active";

/**
 * Name of the notification for switch clicked and activated.
 */
var SWITCH_EVENT = "onSwitch";

FilterStd.prototype = {
  /**
   * @constructor
   *
   * @param  {String|HTMLElement} elContainer  Element or string id.
   * @param  {Object}  events
   */
  init: function (elContainer, events) {
    var sEvent, i, tabs;

    this.eventDispatcher = new EventDispatcher();
    if (events) {
      for (sEvent in events) {
        this.eventDispatcher.connect(sEvent, events[sEvent]);
      }
    }

    this.currentTab = null;

    this.evtDel = new EventDelegator(elContainer, "click");
    this.evtDel.on("JSFilterStd", this.onClick, this);

    let $tabs = $$("a.JSFilterStd", elContainer);
    for (i = 0; i < $tabs.length; i++) {
      if (/uiFilterStd-(\S+)/.test($tabs[i].className)) {
        var tabId = RegExp.$1,
          elLink = $tabs[i];
        if (elLink.classList.contains(ACTIVE)) {
          this.currentTab = elLink;
        }
      }
    }
  },

  destroy: function () {
    this.evtDel.destroy();
    this.evtDel = null;

    this.eventDispatcher.destroy();
    this.eventDispatcher = null;
  },

  onClick: function (ev, el) {
    var tabId = "";

    console.log("FilterStd::onClick()");

    if (/uiFilterStd-(\S+)/.test(el.className)) {
      tabId = RegExp.$1;
    }

    console.assert(tabId !== "", "FilterStd tabId is invalid.");

    if (el === this.currentTab) {
      // already selected
      return false;
    }

    if (this.currentTab) {
      this.currentTab.classList.remove(ACTIVE);
    }

    this.currentTab = el;
    el.classList.add(ACTIVE);

    this.eventDispatcher.notify(SWITCH_EVENT, tabId);

    return false;
  },
};

export default FilterStd;
