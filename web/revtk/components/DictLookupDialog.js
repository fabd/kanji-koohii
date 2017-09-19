/**
 * DictLookupDialog
 *
 * Dictionary lookup for a given character.
 * 
 *  init() crèer le dialogue et l'affiche
 *  hide() 
 *  show()
 *  load()   load another result (avoids recreating dialog and maintains drag/drop position)
 *       

 * yuicompressor globals:
 *
 *   Koohii    vue-bundle, Koohii.UX.(ComponentName)
 * 
 */
/*global YAHOO, window, alert, console, document, Core, App, Koohii */

(function(){

  App.Ui.DictLookupDialog = Core.make();

  var Y = YAHOO,
      Dom = Y.util.Dom;

  App.Ui.DictLookupDialog.prototype =
  {
    /**
     * 
     * 
     */
    init: function(url, ucsId)
    {
      // use unique id to find when we need to reload the dialog
      this.ucsId = ucsId;

      this.requestUri = url;

      var dlgopts = {
        requestUri:  this.requestUri,
        requestData: { ucs: ucsId },
        //invisMask:   true,
        width:       300,
        skin:        "rtk-skin-dlg",
        //context:     options.context,
        scope:       this,
        events:      {
          onDialogResponse: this.onDialogResponse,
          onDialogDestroy:  this.onDialogDestroy,
          onDialogHide:     this.onDialogHide
        }
      };

      this.dialog = new Core.Ui.AjaxDialog(null, dlgopts);
      this.dialog.show();
    },

    load: function(ucsId)
    {
      // Don't load the same kanji twice in a row
      if (this.ucsId === ucsId) {
        return;
      }

      // FIXME  here ideally we should $destroy the Vue instance
      

      // this is a bit hacky, we are using the ancestor AjaxPanel class underlying AjaxDialog, should use AjaxDialog API
      this.dialog.getBody().innerHTML = Core.Ui.AjaxDialog.DIALOG_LOADING_HTML;
      this.ucsId = ucsId;
      this.dialog.getAjaxPanel().get({ucs: ucsId}, this.requestUri);
    },

    show: function()
    {
      Core.log('DictLookupDialog::show()');
      this.dialog.show();
    },
    
    hide: function()
    {
      this.dialog.hide();
    },

    onDialogHide: function()
    {
      Core.log('DictLookupDialog::hide()');

      // keep the dialog in the page
      return false;
    },

    // tron message, cf. core-json.js
    onDialogResponse: function(tron)
    {
      Core.log('DictLookupDialog::onDialogResponse()');

      var props = tron.getProps();

      var mountPoint = this.dialog.getBody().querySelector('div'); // replace the loading div

      Koohii.UX.KoohiiDictList.mount({ items: props.items }, mountPoint);
    },
    
    onDialogDestroy: function()
    {
    },

    isVisible: function()
    {
      return this.dialog.isVisible();
    }
  };

}());
