/**
 * KoohiiNav
 *
 * Lint:
 *   jshint --config=batch/tools/jshint/jshint.conf.json web/revtk/components/KoohiiNav.js
 */
/*global YAHOO, Core, App */

(function(){

  var Y = YAHOO,
      Dom = Y.util.Dom;

  // semi globals (cough)
  var
      // mobile only <nav id="k-nav_m">
      _navRoot = null;

  // constants

  App.Ui.KoohiiNav =
  {
    dropdown:     null,
    elTouched:    null,

    panes:        [],

    init: function()
    {
      console.log('KoohiiNav::init()');

      // desktop nav
      var eventDel = new Core.Ui.EventDelegator(document.body, ['click', 'touchend', 'mouseover', 'mouseout']);
      eventDel.on('JsHasDropdown', this.onDropdown, this);
      eventDel.onDefault(this.onClick, this);
    },

    onDropdown: function(ev, el)
    {
      var data, elDropdown;

      elDropdown = el.getElementsByTagName('ul')[0];

      // toplevel LI which contains a dropdown
      var toplevel = el;

      // console.log('evtype on %o ' + ev.type, el);
  
      if (ev.type === 'touchend')
      {
        if (this.elTouched && toplevel !== this.elTouched)
        {
          if (this.dropdown)
          {
            this.toggleDropdown(false);
          }
        }
        
        this.elTouched = toplevel;

        // the clicked element in the dropdown descendants
        var elTarget = Y.util.Event.getTarget(ev); // A tag
        var elTargetLI = Dom.getAncestorByTagName(elTarget, 'li');
// console.log('el1 '+elTarget.tagName+' el2 '+elTargetLI.tagName);
        if (elTargetLI === toplevel)
        {
          if (this.dropdown)
          {
            // console.log('LAUNCH LI ... ' + elTarget.href);
            window.location.href = elTarget.href;
            return false;
          }
          else
          {
            this.dropdown = elDropdown;
            this.toggleDropdown(true);
            // prevent default ... (below)
            // console.log('should STOP EVENTS HERE');
          }
        }
        else
        {
          // console.log('LAUNCH DROP ... ' + elTarget.href);
          window.location.href = elTarget.href;
          // prevent default ... (below)
        }
    
        /* prevent delay and simulated mouse events */
        ev.preventDefault();
        ev.stopPropagation();
        return false;
      }
      
      //
      // Desktop events
      //

      if (this.elTouched && this.elTouched === toplevel)
      {
        // ignore tous les events pour le même dropdown qui a recu un touch event !
        // console.log('On ignore cet event car touch!');
        //ev.preventDefault();
        return false;
      }

      if (this.dropdown && ev.type === 'click')
      {
        // let browser handle the links
        return true;
      }

      if (ev.type === 'mouseout' && this.dropdown)
      {
        this.toggleDropdown(false);
      }
      else if (ev.type === 'mouseover')
      {
        this.dropdown = elDropdown;
        this.toggleDropdown(true);
      }

      return false;
    },

    // this event handler clears the dropdown if clicking/tapping outside
    onClick: function(ev)
    {
      if (ev.type !== 'click')
      {
        return true;
      }

      if (this.dropdown)
      {
        this.toggleDropdown(false);
      }

      return true;
    },

    toggleDropdown: function(bVisible)
    {
      Dom.setStyle(this.dropdown, 'visibility', bVisible ? 'visible' : 'hidden');
      if (!bVisible)
      {
        this.dropdown = null;
      }
    }
  };

  Core.ready(function(){
    App.Ui.KoohiiNav.init();
  });

}());
