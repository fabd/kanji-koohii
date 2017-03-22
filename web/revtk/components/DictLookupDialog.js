/**
 * DictLookupDialog
 *
 * Dictionary lookup for a given character.
 * 
 * init() crèer le dialogue et l'affiche
 * hide() 
 * show()
 * load() pour charger le contenu avec un autre kanji (évite de détruire et recréer le dialogue, et
 *        maintient sa position drag-drop)
 * 
 */
/*global YAHOO, window, alert, console, document, Core, App */

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
        skin:        "rtk-skin-dlg",
        //context:     options.context,
        scope:       this,
        events:      {
          onDialogInit:    this.onDialogInit,
          onDialogDestroy: this.onDialogDestroy,
          onDialogHide:    this.onDialogHide
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

      // clear the old html while loading
      this.dialog.getBody().innerHTML = '<div id="JSEditStoryLoading">&nbsp;</div>';
      
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

    onDialogInit: function()
    {
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
