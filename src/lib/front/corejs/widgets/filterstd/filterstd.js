/**
 * FilterStd manages a "switch" where the user can toggle between multiple buttons.
 * 
 * Setup:
 *   Each A tag within the container must have class JSFilterStd and a unique identifier
 *   in the form "uiFilterStd-xxx" where "xxx" is passed to the listener.
 * 
 * Events:
 *   onSwitch(id)     Fires AFTER the tab is active, id is the identifier from the classname.
 *
 * Code:
 *   filt = new Core.Widgets.FilterStd('my-filter', { onSwitch: Core.bind(this.callback, this) });
 * 
 * @author   Fabrice Denis
 */
/*global alert, console, document, window, App, Core, YAHOO */

(function(){

  Core.Widgets.FilterStd = Core.make();

  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  /**
   * Class name set on the active tab.
   */
  var ACTIVE = 'active';

  /**
   * Name of the notification for switch clicked and activated.
   */
  var SWITCH_EVENT = 'onSwitch';

  Core.Widgets.FilterStd.prototype =
  {
    /**
     * @constructor
     *
     * @param  {String|HTMLElement} elContainer  Element or string id.
     * @param  {Object}  events
     */
    init: function(elContainer, events)
    {
      var sEvent, i, tabs;

      this.eventDispatcher = new Core.Ui.EventDispatcher();
      if (events)
      {
        for (sEvent in events)
        {
          this.eventDispatcher.connect(sEvent, events[sEvent]);
        }
      }
  
      this.currentTab = null;
 
      this.evtDel = new Core.Ui.EventDelegator(elContainer, "click");
      this.evtDel.on("JSFilterStd", this.onClick, this);

      tabs = Dom.getElementsByClassName('JSFilterStd', 'a', elContainer);
      for (i = 0; i < tabs.length; i++)
      {
        if (/uiFilterStd-(\S+)/.test(tabs[i].className))
        {
          var tabId = RegExp.$1,
              elLink = tabs[i];
          if (Dom.hasClass(elLink, ACTIVE))
          {
            this.currentTab = elLink;
          }
        }
      }
    },
    
    destroy: function()
    {
      this.evtDel.destroy();
      this.evtDel = null;

      this.eventDispatcher.destroy();
      this.eventDispatcher = null;
    },
    
    onClick: function(ev, el)
    {
      var tabId = '';

      Core.log('FilterStd::onClick()');

      if (/uiFilterStd-(\S+)/.test(el.className))
      {
        tabId = RegExp.$1;
      }

      Core.assert(tabId !== '', "FilterStd tabId is invalid.");

      if (el === this.currentTab)
      {
        // already selected
        return false;
      }
      
      if (this.currentTab) {
        Dom.removeClass(this.currentTab, ACTIVE);
      }
  
      this.currentTab = el;
      Dom.addClass(el, ACTIVE);

      this.eventDispatcher.notify(SWITCH_EVENT, tabId);
      
      return false;
    }
  };
  
}());


