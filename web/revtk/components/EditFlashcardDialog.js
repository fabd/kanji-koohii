/**
 * EditFlashcardDialog
 *
 * Dialog to edit a flashcard status and contents.
 *
 * Options
 *   context         Sets the context element to align the dialog (see YUI2 Overlay).
 *   events          Listeners to register (see below)
 *   scope           Scope for the event listeners (OPTIONAL)
 *
 * Notifications:
 *   onMenuResponse  Notified after received an ajax response, with value of TRON property "result"
 *   onMenuItem      Notified with value of attribute "data-menuid" of clicked menu item
 *                   Return true to close the dialog, false to proceed with the ajax post
 *   onMenuHide      Notified when the dialog is hidden or closed.
 *
 * TRON Response:
 *   result          Message from server to indicate the result of the clicked menu
 *   reload          If set, the server tells client to refresh (ie. reload) the dialog
 *
 * Builtin menu actions (value of the attribute "data-menuid" of the menu item element):
 *   close           Notifies onMenuHide and hides the dialog
 *   page            Loads a new page immediately, sets window.location.href to the value of the
 *                   clicked item's data-uri attribute.
 *
 */
/*global YAHOO, window, alert, console, document, Core, App */

(function(){

  App.Ui.EditFlashcardDialog = Core.make();

  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event,
      EditFlashcardDialog = App.Ui.EditFlashcardDialog;

  EditFlashcardDialog.prototype =
  {
    /**
     * 
     * @constructor
     */
    init: function(uri, params, context, options)
    {
      Core.log("EditFlashcardDialog(%s)", uri);

      var mobile = Core.Ui.Mobile.isMobile();

      this.params  = params;
      this.uri     = uri;
      this.options = options;

      this.dlgOpts = {
        requestUri:  uri,
        requestData: params,
        skin:        mobile ? "rtk-mobl-dlg" : "rtk-skin-dlg",
        mobile:      mobile,
        close:       true, //!mobile,
        width:       270,
        scope:       this,
        events:      {
          onDialogHide:    this.onHide,
          onDialogResponse:this.onResponse,
          onDialogSuccess: this.onSuccess
        }
      };

      if (!mobile)
      {
        this.dlgOpts.context = context;
      }

      // register events
      this.eventDispatcher = new Core.Ui.EventDispatcher();
      if (options.events) {
        var events = options.events, eventName;
        for (eventName in events) {
          // if scope is undefined, it will be ignored
          this.eventDispatcher.connect(eventName, events[eventName], options.scope);
        }
      }

      this.show();
    },

    destroy: function()
    {
      this.dialog.destroy();
      this.dialog = null;
      this.eventDispatcher.destroy();
    },

    /**
     * Show again, after it is closed with the YUI close button.
     */
    show: function()
    {
      if (this.dialogRefresh)
      {
        this.dialog.destroy();
        this.dialog = null;
        this.dialogRefresh = false;
      }

      if (!this.dialog)
      {
        this.dialog = new Core.Ui.AjaxDialog(null, this.dlgOpts);
        this.dialog.on('JsMenuItem', this.onMenuItem, this);
      }

      this.dialog.show();
    },

    onHide: function()
    {
      Core.log('EditFlashcardDialog::onHide()');
     
      // clumsy page reload uri received from last response TRON "reload" property
      if (this.reload)
      {
        window.location.href = this.reload;
        return false;
      }

      this.eventDispatcher.notify('onMenuHide');

      // returns false to prevent the dialog from being destroyed.
      return false;
    },

    onResponse: function(t)
    {
      var props = t.getProps();
      if (props.result)
      {
        // flashcard state changed so dialog will reload
        this.dialogRefresh = true;
        this.eventDispatcher.notify('onMenuResponse', props.result);
      }

      this.reload = props.reload;
    },

    onMenuItem: function(ev, el)
    {
      var data   = Dom.getDataset(el),
          panel = this.dialog.getAjaxPanel();

      if (data.menuid === 'page')
      {
        // this menuitem forwards to another page
        window.location.href = data.uri;
        return false;
      }
      else if (data.menuid === 'close')
      {
        // this menuitem closes the dialog
        this.dialog.hide();
        return false;
      }
      else if (this.eventDispatcher.hasListeners('onMenuItem') && this.eventDispatcher.notify('onMenuItem', data.menuid))
      {
        // menu item listener returns true to close dialog
        this.dialog.hide();
        return false;
      }

      // post the base params and the menuid
      var params = Y.lang.merge(this.params, {"menu": data.menuid});
      panel.post(params, this.dlgOpts.requestUri);
      return false;
    }
  };

}());

