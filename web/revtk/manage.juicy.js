/**
 * Manage pages.
 * 
 * @author  Fabrice Denis
 */
/*global window, alert, console, document, window, App, Core, YAHOO */

/* =require from "%WEB%" */
/* !require "/revtk/bundles/yui-base.juicy.js" déjà inclus */
/* =require "/revtk/components/EditKeywordDialog.js" */

/* =require from "%CORE%" */
/* =require "/widgets/selectiontable/selectiontable.js" */

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

      this.initView('manage-view');
        
      // Cancel/Reset buttons on ajax forms
      bodyED.on("JSManageCancel", function(e, el) { return that.load(el, {'cancel':true}); });
      bodyED.on("JSManageReset", function(e, el) { return that.load(el, {'reset':true}); });

      // Manage > Edit Keywords
      var el = Dom.get('EditKeywordsTableComponent');
      if (el)
      {
        this.ajaxTable = new Core.Widgets.AjaxTable(el);
        this.editKeywordUri = Dom.getDataset(el).uri;
        bodyED.on("JSEditKeyword", this.onEditKeyword, this);
      }
    },

    initView: function(viewId)
    {
      this.viewDiv = Dom.down(viewId, 'ajax');
      
      if (this.viewDiv)
      {
        this.viewPanel = new Core.Ui.AjaxPanel(this.viewDiv, {
          bUseShading: false,
          initContent: true,
          form:        'main-form',
          events: {
            'onSubmitForm':     Core.bind(this.onSubmitForm, this),
            'onContentInit':    Core.bind(this.onContentInit, this),
            'onContentDestroy': Core.bind(this.onContentDestroy, this)
          }
        });
      }
    },

    onContentInit: function()
    {
      var i;

      Core.log('onContentInit()');

      var el = this.elSelectionTable = Dom.down(this.viewDiv, 'selection-table');
      if (el)
      {
        // clear checkboxes in case of page refresh
        var els = Dom.getElementsByClassName('checkbox', 'input', el);
        for (i = 0; i < els.length; i++) {
          els[i].checked = false;
        }
        
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
      var data, options, that = this;

      // @param  {String}   keyword 
      // @param  {Boolean}  next (optional)
      function callback(keyword, next)
      {
        var tr, td, nextRow, nextEl;

        Core.log('EditKeywordComponent callback');
        
        // get the custkeyword td
        tr = Dom.getAncestorByTagName(el, "tr");
        td = Dom.down(tr, "JSCkwTd");
        td.innerHTML = keyword;

        // force reload
        that.oEditKeyword.destroy();
        that.oEditKeyword = null;

        if (next)
        {
          Core.log('Edit next keyword...');
          nextRow = Dom.getNextSibling(tr);
          if (nextRow)
          {
            nextEl = Dom.getElementsByClassName('JSEditKeyword', 'img', nextRow)[0];
            window.setTimeout(function(){ that.onEditKeyword(null, nextEl); }, 200);
          }
        }
      }

      // just show dialog if clicking the same keyword twice, otherwise load

      data = Dom.getDataset(el);
      if (!this.oEditKeyword || data.id !== this.editKeywordId)
      {
        var contextEl = Dom.getAncestorByTagName(el, 'td');
        
        options = { 
          context: [contextEl, "tr", "tr", null, [0, 0]],
          params:  { id: data.id, manage: true } /* manage: use the "Save & Next" chain editing */
        };

        // FIXME ideally should call this.oEditKeyword.destroy() here if it is set

        this.oEditKeyword = new App.Ui.EditKeywordComponent(this.editKeywordUri, options, callback);
        this.editKeywordId = data.id;
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

