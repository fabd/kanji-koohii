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

      var mobile = Core.Ui.Mobile.isMobile();

      var dlgopts = {
        requestUri:  this.requestUri,
        requestData: { ucs_code: ucsId, reviewMode: true },
        skin:        mobile ? "rtk-mobl-dlg" : "rtk-skin-dlg",
        mobile:      mobile,
        close:       !mobile,
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
      if (!mobile)
      {
        dlgopts.context = ["uiFcMain", "tl", "tl", null, [-10, -36]];
      }

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
      this.dialog.getBody().innerHTML = '<div style="width:480px" class="body ajax-loading"></div>';
      
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
