/**
 * Manage pages.
 * 
 */

/* =require from "%WEB%" */
/* =require "/revtk/components/EditKeywordDialog.js" */
/* =require from "%CORE%" */
/* =require "/widgets/selectiontable/selectiontable.js" */

import $$ from "@lib/koohii/dom";
import App from "@old/app.js";

App.ready(function()
{
  var Y = YAHOO,
      Dom = Y.util.Dom;

  App.ManageFlashcards =
  {
    init: function()
    {
      var that = this, 
          bodyED = App.getBodyED();

      this.initView('#manage-view .ajax');
        
      // Cancel/Reset buttons on ajax forms
      bodyED.on("JSManageCancel", function(e, el) { return that.load(el, {'cancel':true}); });
      bodyED.on("JSManageReset", function(e, el) { return that.load(el, {'reset':true}); });

      // Manage > Edit Keywords
      var el = Dom.get('EditKeywordsTableComponent');
      if (el)
      {
        this.ajaxTable = new Core.Widgets.AjaxTable(el);
        this.editKeywordUri = el.dataset.uri;
        bodyED.on("JSEditKeyword", this.onEditKeyword, this);
      }
    },

    initView: function(viewId)
    {
      this.viewDiv = $$(viewId)[0];
      
      if (this.viewDiv)
      {
        this.viewPanel = new Core.Ui.AjaxPanel(this.viewDiv, {
          bUseShading: false,
          initContent: true,
          form:        'main-form',
          events: {
            'onSubmitForm':     this.onSubmitForm.bind(this),
            'onContentInit':    this.onContentInit.bind(this),
            'onContentDestroy': this.onContentDestroy.bind(this)
          }
        });
      }
    },

    onContentInit: function()
    {
      var i;

      console.log('onContentInit()');

      var el = this.elSelectionTable = $$('.selection-table', this.viewDiv)[0];
      if (el)
      {
        // clear checkboxes in case of page refresh
        $$('.checkbox', el).each((el, i) => {
          el.checked = false;
        });

        this.selectionTable = new Core.Widgets.SelectionTable(el);
      }
    },

    onContentDestroy: function()
    {
      if (this.selectionTable) {
        this.selectionTable.destroy();
        this.selectionTable = null;
      }
    },
    
    onSubmitForm: function(oEvent)
    {
      var data = this.selectionTable ? this.selectionTable.getPostData() : null;

      this.viewPanel.post(data);

      return false;
    },

    load: function(element, params)
    {
      this.viewPanel.post(params);
      return false;
    },

    /**
     * Open the Edit Keyword dialog for keywords in the Manage > Edit Keywords table.
     *
     */
    onEditKeyword: function(e, el)
    {
      var options, that = this;

      // @param  {String}   keyword 
      // @param  {Boolean}  next (optional)
      function callback(keyword, next)
      {
        var tr, td, nextRow, nextEl;

        console.log('EditKeywordComponent callback');
        
        // get the custkeyword td
        tr = Dom.getAncestorByTagName(el, "tr");
        td = $$(".JSCkwTd", tr)[0];
        td.innerHTML = keyword;

        // force reload
        that.oEditKeyword.destroy();
        that.oEditKeyword = null;

        if (next)
        {
          console.log('Edit next keyword...');
          nextRow = Dom.getNextSibling(tr);
          if (nextRow)
          {
            nextEl = Dom.getElementsByClassName('JSEditKeyword', 'img', nextRow)[0];
            window.setTimeout(function(){ that.onEditKeyword(null, nextEl); }, 200);
          }
        }
      }

      // just show dialog if clicking the same keyword twice, otherwise load

      var ucsId = el.dataset.id;
      if (!this.oEditKeyword || ucsId !== this.editKeywordId)
      {
        var contextEl = Dom.getAncestorByTagName(el, 'td');
        
        options = { 
          context: [contextEl, "tr", "tr", null, [0, 0]],
          params:  { id: ucsId, manage: true } /* manage: use the "Save & Next" chain editing */
        };

        // FIXME ideally should call this.oEditKeyword.destroy() here if it is set

        this.oEditKeyword = new App.Ui.EditKeywordComponent(this.editKeywordUri, options, callback);
        this.editKeywordId = ucsId;
      }
      else
      {
        this.oEditKeyword.show();
      }

      return false;
    }
  };

  App.ManageFlashcards.init();

});

