/**
 * Core.Ui.Mobile helpers.
 * 
 *   isMobile()    returns true if display viewport width is small and whether we should use optimized styles/behaviour.
 *                 FIXME   très vague...
 * 
 */
/* globals Core, App, YAHOO */

if (typeof(Core) === 'undefined') {
  throw new Error('Core or YUI is not declared');
}

/**
 * Core.Ui Helpers
 * 
 * These are global helpers related to the DOM and user interface. 
 */
(function(){
  
  Core.Ui.Mobile =
  {
    /**
     * Checks whether viewport width is below threshold where we want to use optimized
     * styles and behaviours (eg. edge to edge dialogs, larger buttons).
     * 
     * @return {boolean}
     */
    isMobile: function()
    { 
      return (window.innerWidth <= 720);
    }
  };

}());
