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
/*global YAHOO, window, alert, console, document, Core, App */

(function(){

  App.Ui.EditStoryDialog = Core.make();

  var Y = YAHOO,
      Dom = Y.util.Dom;

  var isMobile = Core.Ui.Mobile.isMobile();

  App.Ui.EditStoryDialog.prototype =
  {
    // App.Ui.EditStoryComponent created by onDialogInit()
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
        width:       480,
        scope:       this,
        events:      {
          onDialogInit:    this.onDialogInit,
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
      Core.log("addCloseButton()");

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

      var sLoadingHTML = '<div style="min-width:200px" class="body JsAjaxDlgLoading"></div>';

      // clear the old html while loading
      this.dialog.getBody().innerHTML = sLoadingHTML;

      if (isMobile) {
        this.addCloseButton();
      }

      this.ucsId = ucsId;
      
      this.dialog.getAjaxPanel().get({ucs_code: ucsId, reviewMode: true}, this.requestUri);
    },

    show: function()
    {
      Core.log('EditStoryDialog::show()');
      this.dialog.show();
    },
    
    hide: function()
    {
      this.dialog.hide();
    },

    onDialogHide: function()
    {
      Core.log('EditStoryDialog::hide()');
      if (this.editStory.isEdit())
      {
        this.editStory.doCancel();
      }

      // keep the dialog in the page
      return false;
    },
    
    onDialogInit: function()
    {
      var elBody = this.dialog.getBody();
      this.editStory = new App.Ui.EditStoryComponent(elBody);
    },
    
    onDialogDestroy: function()
    {
      if (this.editStory)
      {
        this.editStory.destroy();
        this.editStory = null;
      }
    },

    isVisible: function()
    {
      return this.dialog.isVisible();
    }
    
    /* Pas besoin de destroy durant ou à la fin d'une review.
    destroy: function()
    {
      this.hide();
      
      if (this.editStory)
      {
        this.editStory.destroy();
        this.editStory = null;
      }

      if (this.ajaxPanel)
      {
        this.ajaxPanel.destroy();
        this.ajaxPanel = null;
      }
    }*/

  };

}());
