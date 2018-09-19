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
        context:     [document.body, "tl", "tl", null, [1, 1]],  // YUI2 container "context" option
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

      // ^ grab "known kanji" from the FLashcard Review page, since the state is preserved
      // during the entire review session, it's more efficient this way. The user's known
      // kanji could realistically be 2000 to 3000 utf8 characters. So even though they
      // are also cached in php session, it's better to avoid returning several KBs of data
      // with each dictionary lookup request
      //
      var vueProps = {
        items:       props.items,
        known_kanji: Koohii.UX.reviewMode.fc_known_kanji   // ^
      }
      var elMount  = this.dialog.getBody().querySelector('div'); // replace the loading div
      console.log('caca %o', elMount)
      var instance = VueInstance(Koohii.UX.KoohiiDictList, elMount, vueProps, true);
      console.log('caca2')
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
