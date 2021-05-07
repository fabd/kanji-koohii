/**
 * DictLookupDialog  -- REFACTORING AT SOME POINT TO A VUE-BASED DIALOG
 *
 *  show()
 *  load()   load another result (avoids recreating dialog and maintains drag/drop position)
 *       
 */
/* globals YAHOO, Core, App, VueInstance */

(function(){

  App.Ui.DictLookupDialog = Core.make();
  
  const isMobile = (window.innerWidth <= 720);

  var Y = YAHOO,
      Dom = Y.util.Dom;

  App.Ui.DictLookupDialog.prototype =
  {
    // unique id to find when we need to reload the dialog
    ucsId: 0,

    // instance of Koohii.UX.KoohiiDictList, if created
    vueInst: null,

    /**
     * 
     * 
     */
    init: function()
    {
      this.ucsId = 0;

      var dlgopts = {
        skin:        isMobile ? "rtk-mobl-dlg" : "rtk-skin-dlg",
        mobile:      isMobile,
        scope:       this,
        events:      {
          onDialogDestroy:  this.onDialogDestroy,
          onDialogHide:     this.onDialogHide
        }
      };

      if (!isMobile) {
         dlgopts.context = [document.body, "tl", "tl", null, [1, 1]];  // YUI2 container "context" option
      }

      this.dialog = new Core.Ui.AjaxDialog(null, dlgopts);
      this.dialog.show();

      // hack-ish (legacy code) -- we need a mount point
      this.dialog.yPanel.setBody('<div class="JsMount" style="min-height:100px;background:red;"></div>');
    },

    load: function(ucsId)
    {
      // Don't load the same kanji twice in a row
      if (this.ucsId === ucsId) {
        return;
      }

      if (!this.vueInst) {
        var elMount = this.dialog.getBody().querySelector('.JsMount');
        this.vueInst = VueInstance(Koohii.UX.KoohiiDictList, elMount, {}, true);
      }

      this.vueInst.load(ucsId);

      // note: this also prevents spamming load() while ajax is in progress
      this.ucsId = ucsId;
    },

    show: function()
    {
      console.log('DictLookupDialog::show()');
      this.dialog.show();
    },
    
    hide: function()
    {
      this.dialog.hide();
    },

    onDialogHide: function()
    {
      console.log('DictLookupDialog::hide()');

      // keep the dialog in the page
      return false;
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
