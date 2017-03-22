/**
 * Custom console that works in all browsers and allows to log the printf() style
 * arguments supported by the FireBug console API (when running in Firefox+Firebug)
 * 
 * Provides Core.log() method.
 *   
 * Example:
 *  Core.log("result: %o", element);
 *  
 * Note:
 * - The custom console is meant to work in any page without Javascript dependencies
 * - The Core.log proxy method can be easily changed.
 * - The printf style arguments will only work in Firebug's console, and ignored in IE
 *
 * @see       http://getfirebug.com/console.html 
 * @author    Fabrice Denis
 */
/*global YAHOO, alert, console, document, window, Core */

(function() {

  var lineNr  = 0,
      isDOMReady = !!document.getElementsByTagName('body')[0],
      history = [],
      div     = false,
      itsIE   = null;

  function isIE()
  {
    return itsIE === null ? (itsIE = !!document.getElementById('ie')) : itsIE;
  }

  /**
   * Dynamically add the logger output div to the page.
   * 
   */
  function build()
  {
    var top, hd, bd;
      
    top = document.createElement('div');
    top.appendChild((hd = document.createElement('div')));
    top.appendChild((bd = document.createElement('div')));
    
    top.style.position = 'absolute';
    top.style.right = '0';
    top.style.top = '0';
    top.style.padding = '2px';
    top.style.background = '#ff4040';
    top.style.color = '#fff';
    top.style.font = '11px/13px "Courier New", monospace';
    top.style.width = '16px';
    top.style.height = '16px';
    top.style.overflow = 'hidden';
    top.style.cursor = 'pointer';
    
    hd.style.padding = '0 0 2px';
    hd.style.display = 'none';
    bd.style.display = 'none';
    
    bd.style.background = '#ffe0e0';
    bd.style.color = '#000';
    bd.style.width = '300px';
    bd.style.height = '200px';
    bd.style.overflow = 'scroll';

    hd.innerHTML = "Javascript Debug Log (click to toggle)";
    top.onclick = function()
    {
      var disp = (bd.style.display === 'none') ? 'block' : 'none';
      if (disp === 'block') {
        top.style.width = '300px';
        top.style.height = 'auto';
      }
      else {
        top.style.width = '16px';
        top.style.height = '16px';
      }
      hd.style.display = bd.style.display = disp; 
      return false;
    };
    
    document.getElementsByTagName('body')[0].appendChild(top);

    // Auto-open log for IE debugging
    if (1 || isIE())
    {
      hd.style.display = bd.style.display = 'block';
      top.style.width = '300px';
      top.style.height = 'auto';
    }
    
    return bd;
  }

  function output(message)
  {
    var t = document.createTextNode('' + lineNr + ': ' + message),
        br = document.createElement('br');
    div.appendChild(t);
    div.appendChild(br);
    
    lineNr++;
  }

  /**
   * Log a message to the console, or the dynamically created div.
   * 
   * Accepts sprintf() style arguments, see FireBug console API.
   * 
   * @see    http://getfirebug.com/console.html
   * 
   * @param  {String}  message
   */
  function log()
  {
    var message;

    // DOM ready for the custom console?
    if (!isDOMReady) {
      if (!(isDOMReady = !!document.getElementsByTagName('body')[0])) {
        history.push("[DOM!READY] " + arguments[0]);
        return;
      }
    }
  
    // create a div to output messages
    if (!div) 
    {
      div = build();
      
      while ((message = history.shift())) 
      {
        output(message);
      }

      // Warn if IE8 is not in standards mode.
      if (isIE())
      {
        var compat = parseInt(document.documentMode, 10) || null;
        if (compat !== null && compat !== 8)
        {
          output('WARNING: IE Compatibility not IE8:' + compat);
        }
      }
    }
      
    output(arguments[0]);

    // scroll down
    if (typeof(div.scrollTop) !== "undefined") {
      div.scrollTop = div.scrollHeight;
    }
  }

  // use the Firebug console if Firebug is present (and activated!),
  // otherwise use our custom console to see javascript debug messages in IE etc.
  if (1 && typeof(window.console) !== "undefined" /*&& typeof(window.loadFirebugConsole) !== "undefined"*/)
  {
    Core.log = function() {
      console.log.apply(console, arguments);
    };
  }
  else {
    Core.log = log;
  }

}());
