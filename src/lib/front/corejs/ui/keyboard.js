/**
 * Core.Ui.Keyboard adds simple keyboard shortcut handling with callbacks.
 * 
 * - Only alphanumerical characters.
 * - Control key combos do not trigger the callback so as not to override
 *   the default browser behaviour (eg: Ctrl-N for New Window).
 * - Only one listener for a key at one time.
 * 
 * Options:
 * 
 *   bDisableInInput      Defaults to true, do not call listener when key is pressed
 *                        while INPUT, TEXTAREA or SELECT is active.
 * 
 * Methods:
 *   addListener(key, fnListener)    Key can be a character or a keycode. Only use
 *                                   characters 0-9, a-z, A-Z as string parameter.
 *   removeListener(key)             See addListener()
 *   destroy();
 * 
 * Usage:
 * 
 *   The listener callback receives the Prototype event object as argument.
 * 
 *   Use Prototype's bindAsEventListener to pass a closure to addListener(), and
 *   use the extra argument to identify the key, this is easier than checking the
 *   keycode from the event object.
 *   
 *   addListener('s', Core.bind(this.save, this, 'save'));
 * 
 */
/*global YAHOO, window, alert, console, document, Core, App */


(function (){
  
  Core.Ui.Keyboard = Core.make();
  
  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  Core.Ui.Keyboard.prototype =
  {
    keys: null,
    
    init: function(options)
    {
      // set options and defaults
      options = options ? options : {};
      options.bDisableInInput = options.bDisableInInput!==false;
      this.options = options;

      this.oKeys = [];
      this.evtCache = new Core.Ui.EventCache();
      this.evtCache.addEvent(document, 'keydown', Core.bind(this.evKeydown, this));
    },
    
    destroy: function()
    {
      this.evtCache.destroy();
    },

    /**
     * Always return a keycode.
     *
     * @param  {String|Number}  key   The key as a char (0-9, a-z, A-Z only) or the key code
     * @return {Integer}
     */
    getKeyCode: function(key)
    {
      // charCodeAt() returns the correct keyboard event keycode for uppercase letters only
      return typeof(key) === "number" ? key : key.toUpperCase().charCodeAt(0);
    },

    addListener: function(key, fnListener)
    {
      var keycode = this.getKeyCode(key);
      this.oKeys[keycode] = fnListener;
    },
    
    removeListener: function(key)
    {
      var keycode = this.getKeyCode(key);
      delete this.oKeys[keycode];
    },

    /**
     * Keyboard event handler.
     *
     */
    evKeydown: function(ev)
    {
      var isCtrl, iKeyCode;
      //console.log('Keyboard::evKeydown(%o)', ev.keyCode);

    //  var iKeyCode = window.event ? event.keyCode : ev.keyCode;
    //  var sKeyChar = String.fromCharCode(iKeyCode).toLowerCase();

      // Don't enable shortcut keys in Input, Textarea fields
      if (this.options.bDisableInInput)
      {
        var element = Event.getTarget(ev);
        if (element.nodeType === 3)
        {
          element = element.parentNode;
        }
        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA' || element.tagName === 'SELECT')
        {
          return;
        }
      }

      isCtrl = ev.ctrlKey;
      iKeyCode = ev.keyCode;

      if (!isCtrl)
      {
        var fnListener = this.oKeys[iKeyCode];
        if (fnListener)
        {
          fnListener.call(null, ev);
          Event.stopEvent(ev);
          return false;
        }
      }
      
      return true;
    }
  };

}());

