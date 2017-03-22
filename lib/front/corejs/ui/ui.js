/**
 * Core.Ui Helpers.
 * 
 * Core.Ui.Helper
 *   getParams()                 Parse values from HTML element class names
 *   openAjaxDialog(options)     Simplifies creation of ajax dialog for common cases.
 *   insertTop()                 Insert node at top of document, but within #ie if present to enable IE styles
 *   toggle()                    Toggle display of element (but use "" rather than "block", for TDs etc)
 * 
 * @author Fabrice Denis
 */
/*global window, Core, App, YAHOO, alert, console, document */

if (typeof(Core) === 'undefined') {
  throw new Error('Core or YUI is not declared');
}

Core.Ui = {};
Core.Widgets = {};

/**
 * Core.Ui Helpers
 * 
 * These are global helpers related to the DOM and user interface. 
 */
(function(){
  
  var Y = YAHOO,
      Dom = Y.util.Dom;
  
  Core.Ui.Helper =
  {
    /* OBSOLETE
     * Return parameters and values that are passed through
     * the HTML Element class names.
     * 
     * <pre>
     *   &lt;div class="module module-id-1 module-status-off"&gt; ... &lt;/div&gt;
     *   // => { id: "1", status: "off" }
     * </pre>
     * 
     * @param  {HTMLElement} el     The element
     * @param  {String}   name      The base class name (without dash suffix!)
     * 
     * @return {Object}
    getParams: function(el, name)
    {
      var re = new RegExp("(?:^|\\s)" + name + "-([^-]+)-(\\w+)", "g"),
          obj = {},
          a;
      
      while ((a = re.exec(el.className)))
      {
        var prop = a[1], value = a[2];
        obj[prop] = value;
      }
      
      return obj;
    },
     */
 
    /**
     * Open an AjaxDialog from a given url, or the url found in the clicked link.
     * 
     * This is a generic helper for the most common cases where no complicated
     * handling needs to be done with the dialog response.
     * 
     * Options
     *   srcUrl     {String}        Url to load the dialog from
     *   srcHref    {HTMLElement}   Will use the href attribute of the element as srcUrl
     *   
     *   successUrl {String}        Load given url on the dialog success event (note: this will override
     *                              events.onDialogSuccess, don't use both)
     *
     * Advanced:
     *   events     {Object}        (optional) AjaxDialog listeners, see Core.Ui.AjaxDialog
     *   
     * @param  {Object} options
     * 
     * @return {boolean}        Always returns false (return fn() shortcut, REFACTOR)
     */
    openAjaxDialog: function(options)
    {
      var o = options, 
              dlgOptions = { srcUrl: null },
              ajaxDialog;
      
      Core.log("openAjaxDialog options: ",o);
      
      dlgOptions.requestUri = o.srcHref ? Dom.getAttribute(o.srcHref, "href") : o.srcUrl;
      dlgOptions.timeout = o.timeout ? o.timeout : null; // use default timeout if not specified in options
      dlgOptions.events = options.events ? options.events : {};

      if (o.successUrl) {
        dlgOptions.events.onDialogSuccess = function(){
          window.location.href = o.successUrl;
        };
      }

      ajaxDialog = new Core.Ui.AjaxDialog(null, dlgOptions);
      ajaxDialog.show();

      return false;
    },
    
    /**
     * Insert node as first child of the document, _EXCEPT_ for IE, insert
     * as the first child of the #ie div (which should be first child of document.body).
     * 
     * This ensures that styles targetting IE (#ie .foobar { ... } ) will work
     * on dialogs and other elements dynamically inserted into the page. 
     * 
     * @param {HTMLElement} node
     */    
    insertTop: function(node)
    {
      var elParent = Dom.get("ie") || document.body;
      elParent.insertBefore(node, elParent.firstChild);
    }
  };

}());
