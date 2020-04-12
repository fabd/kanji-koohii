/**
 * EditStoryDialog
 *
 * Edit a story with the EditStoryComponent from inside an ajax dialog.
 * 
 * init() crèer le dialogue et l'affiche
 * hide() pour le cache (si l'utilisateur utilise le shortcut pour "S"tory window)
 * show() pour le r"afficher
 * load() pour charger le contenu avec un autre kanji (évite de détruire et recréer le dialogue, et
 *        maintient sa position drag-drop)
 * 
 * @jslint  jslint web/revtk/components/EditStoryDialog.js
 */
/* globals YAHOO, Core, App, Koohii, VueInstance */

(function(){

  App.Ui.EditStoryDialog = Core.make();

  var isMobile = Core.Ui.Mobile.isMobile();

  var LOADING_WIDTH = 500;

  App.Ui.EditStoryDialog.prototype =
  {
    // the Vue based EditStory component
    editStory: null,

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
        requestData: { ucs_code: ucsId, reviewMode: true },
        skin:        isMobile ? "rtk-mobl-dlg" : "rtk-skin-dlg",
        mobile:      isMobile,
        close:       !isMobile,
        width:       LOADING_WIDTH,
        scope:       this,
        events:      {
          onDialogResponse: this.onDialogResponse,
          onDialogDestroy: this.onDialogDestroy,
          onDialogHide:    this.onDialogHide
        }
      };

      // FIXME position dialog near flashcard to avoid current issue with centering
      // and the "ajax loading" content (ie. we don't know what the real width
      // will be until content is loaded, and we dont want dialog to move around)
      if (!isMobile)
      {
        dlgopts.context = ["uiFcMain", "tl", "tl", null, [-10, -36]];
      }

      this.dialog = new Core.Ui.AjaxDialog(null, dlgopts);
      this.dialog.show();

      // Issue #106
      if (isMobile) {
        this.addCloseButton();
      }
    },

    // Issue #106 / hacky solution but this will be refactored to Vue anyway
    // - we don't want to add a Close button at higher level in AjaxDialog
    // - we add the html for the Close button, it gets replaced by the ajax content
    addCloseButton: function()
    {
      console.log("addCloseButton()");

      var el = document.createElement('div');

      el.innerHTML = 
'<div class="uiBMenu">' +
  '<div class="uiBMenuItem">' +
    '<a class="uiFcBtnGreen JSDialogHide uiIBtn uiIBtnDefault" href="#"><span>Close</span></a>' +
  '</div>' +
'</div>';

      var elBody = this.dialog.getBody();
      elBody.appendChild(el);
    },

    load: function(ucsId)
    {
      // Don't load the same kanji twice in a row
      if (this.ucsId === ucsId) {
        return;
      }

      // cleanup
      this.onDialogDestroy();

      // clear the old html while loading
      this.dialog.setBodyLoading(LOADING_WIDTH);

      if (isMobile) {
        this.addCloseButton();
      }

      this.ucsId = ucsId;
      
      this.dialog.getAjaxPanel().get({ucs_code: ucsId, reviewMode: true}, this.requestUri);
    },

    show: function()
    {
      console.log('EditStoryDialog::show()');
      this.dialog.show();
    },
    
    hide: function()
    {
      this.dialog.hide();
    },

    onDialogHide: function()
    {
      console.log('EditStoryDialog::hide()');

      // fabd: removed "cancel edit mode"... what if user edited, then closed by mistake, 
      //   not really necessary to undo edit mode when the Edit Story dialog is hidden.

      // keep the dialog in the page
      return false;
    },
    
    onDialogResponse: function(tron)
    {
// console.log('ondialogresponse tron %o', tron);

      var data = tron.getProps();

      var vueProps = {
        'kanjiData':       data.kanjiData,
        'custKeyword':     data.custKeyword,

        'isReviewMode':    true,
        'isFavoriteStory': data.isFavoriteStory,

        'postStoryEdit':   data.postStoryEdit,
        'postStoryView':   data.postStoryView,
        'postStoryPublic': data.postStoryPublic
      };

      var elMount = this.dialog.getBody().querySelector('div'); // replace the AjaxDialog's loading div
      this.editStory = Koohii.Refs.vueEditStory = VueInstance(Koohii.UX.KoohiiEditStory, elMount, vueProps, true);
    },
    
    onDialogDestroy: function()
    {
// console.log('onDialogDestroy()')
      if (this.editStory)
      {
        this.editStory.$destroy();
        this.editStory = null;
      }
    },

    isVisible: function()
    {
      return this.dialog.isVisible();
    }

  };

}());
