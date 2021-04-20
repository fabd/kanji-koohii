/**
 * EditKeywordDialog
 *
 */
/* globals YAHOO, Core, App */

(function(){

  App.Ui.EditKeywordComponent = Core.make();

  var Y = YAHOO,
      $$ = Koohii.Dom,
      Dom = Y.util.Dom,
      Event = Y.util.Event,
      EditKeywordComponent = App.Ui.EditKeywordComponent;

  EditKeywordComponent.prototype =
  {
    /**
     *
     * Options:
     *   context    Sets the context element to align the dialog (see YUI2 Overlay).
     *   params     Request data for AjaxDialog: id => ucs code, manage => enable chain editing
     *
     * @param  {int}    id         UCS-2 code.
     * @param  {object} options
     * @param  function callback   Callback to insert the updated keyword back into the page
     */
    init: function(url, options, callback)
    {
      console.log("EditKeywordComponent(%s, %o)", url, options);

      this.options = options;
      this.callback = callback;

      var dlgopts = {
        requestUri:  url,
        requestData: options.params,
        skin:        "rtk-skin-dlg",
        context:     options.context,
        scope:       this,
        events:      {
          onDialogInit:    this.onInit,
          onDialogDestroy: this.onDestroy,
          onDialogSuccess: this.onSuccess,
          onDialogHide:    this.onHide
        }
      };

      this.dialog = new Core.Ui.AjaxDialog(null, dlgopts);
      this.dialog.on("reset", this.onReset, this);
      this.dialog.show();
    },

    // Show again, after it is closed with the YUI close button.
    show: function()
    {
      this.dialog.show();
      this.focus();
    },

    destroy: function()
    {
      this.dialog.destroy();
      this.dialog = null;
    },

    focus: function()
    {
      var el = this.getInput();
      el.focus();
      el.select();
    },

    onInit: function(t)
    {
      this.props = t.getProps();
      
      // listener for the TAB key (chain edit on the Manage page)
      this.evtCache = new Core.Ui.EventCache();
      this.evtCache.addEvent(this.getInput(), 'keydown', this.onKeyDown.bind(this));
      
      this.focus();
    },

    onDestroy: function()
    {
      this.evtCache.destroy();
      this.evtCache = null;
    },

    onKeyDown: function(e)
    {
      // TAB key
      if (e.keyCode === 9) 
      {
        this.dialog.getAjaxPanel().post({ "doNext": true });
        Event.stopEvent(e);
        return false;
      }

      return true;
    },

    onHide: function()
    {
      // keep the dialog in the page
      return false;
    },

    // Copy keyword back into the main page
    // If JsTron property "next" is returned, the callback for the Manage page edits the next keyword
    onSuccess: function(t)
    {
      var props = t.getProps();
      this.callback(props.keyword, props.next);
    },

    onReset: function(e, el)
    {
      var input = this.getInput();
      input.value = this.props.orig_keyword;
      input.focus();
      return false;
    },

    getInput: function()
    {
      return $$(".txt-ckw", this.dialog.getBody())[0];
    }
  };

}());

