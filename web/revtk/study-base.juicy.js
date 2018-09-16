/**
 * Study page - includes (Still using old prototype-based javascript)
 * 
 * yuicompressor globals:
 *
 *   Vue       vue-bundle, VueJs
 *   Koohii    vue-bundle, Koohii.UX.(ComponentName)
 *   
 */
/*global YAHOO, window, alert, console, document, Core, App, $, $$, Event, actb, kwlist, kklist, Vue, Koohii*/

/* =require from "%WEB%" */
/* !require "/revtk/bundles/yui-base.juicy.js" déjà inclus */

/* =require from "%FRONT%" */
/* =require "/scripts/autocomplete.js" */

/* =require from "%WEB%" */
/* !require "/revtk/study/keywords.js" */
/* =require "/revtk/components/EditKeywordDialog.js" */
/* =require "/revtk/components/EditFlashcardDialog.js" */
/* =require "/revtk/components/SharedStoriesComponent.js" */

(function(){

  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  App.StudyPage =
  {
    /**
     *
     * Options:
     *   URL_SEARCH             
     */
    initialize:function(options)
    {
      var el;

      // otpions & php constants
      this.options = options;
      
      // references
      this.elSearch = Dom.get('txtSearch');
      
      // quick search autocomplete
      var actb1 = this.actb1 = new actb(this.elSearch, kwlist);
      actb1.onChangeCallback = Core.bind(this.quicksearchOnChangeCallback, this);
      actb1.onPressEnterCallback = Core.bind(this.quicksearchEnterCallback, this);

      // function move to _SideColumnView.php for CJK lang attributes
      actb1.actb_extracolumns = this.actb_extracols; // _SideColumnView.php
      /*
      function(iRow) {
        return '<span class="f">'+(iRow+1)+'</span><span class="k cj-t">&#'+kklist.charCodeAt(iRow)+';</span>';
      };*/

      // clicking in quick search box selects the text
      Event.on(this.elSearch, 'focus', function(ev)
      {
        var el = Event.getTarget(ev);
        if (el.value !== '')
        {
          el.select();
        }
      });

      // auto focus search box
      if (this.elSearch && this.elSearch.value ==='')
      {
        this.elSearch.focus();
      }


      if ((el = Dom.get('DictStudy')))
      {
        this.initDictionary(el);
      }

      if ((el = Dom.get('SharedStoriesComponent')))
      {
        this.sharedStoriesComponent = new App.Ui.SharedStoriesComponent(this, el);
      }

      if ((el = Dom.get('EditFlashcard')))
      {
        this.elEditFlashcard = el;
        var ed = new Core.Ui.EventDelegator(el, "click");
        ed.on("JsEditFlashcard", this.onEditFlashcard, this);
      }

      /* clear learned kanji list
      if ((el = Dom.get('JsLearnedComponent'))) {
        var panel = new Core.Ui.AjaxPanel(el, {initContent: true});
      }*/
    },

    initDictionary: function(el)
    {
      var elHead  = Dom.get("DictHead");
      Event.on(elHead, "click", this.toggleDictionary, this, true);
      this.dictVisible = false;
      this.dictPanel = null;
    },

    toggleDictionary: function(e, el)
    {
      var visible = !this.dictVisible,
          elBody  = Dom.get("DictBody"),
          elPanel = Dom.get("DictPanel"),
          data    = Dom.getDataset(elPanel);

      var that = this;

      if (!this.dictPanel) {  

        var onAjaxResponse = function(o) {
          Core.log('Dict onAjaxResponse(%o)', o);
          that.dictPanel = true;

          var elMount = elPanel.querySelector('div'); // replace the loading div

          var props = o.responseJSON.props;

          Koohii.UX.KoohiiDictList.mount({
            items:       props.items,
            known_kanji: props.known_kanji 
          }, elMount);
        };

        // request known kanji, as it is otherwise not provided in the Study pages
        var ajaxRequest = new Core.Ui.AjaxRequest(data.uri, {
          parameters: {
            "ucs": data.ucs,
            "req_known_kanji": true
          },
          success:    onAjaxResponse,
          scope:      this
        });
      }

      Dom.toggle(elBody, visible);
      this.dictVisible = visible;
    },
    
    onSearchBtn: function(e)
    {
      var text = this.elSearch.value;
      this.quicksearchOnChangeCallback(text);
      Event.stop(e);
      return false;
    },

    onEditFlashcard: function(ev, el)
    {
      var data = Dom.getDataset(el);

      function onMenuResponse(result)
      {
        // update icon to reflect new flashcard state
        var z = {'added': '1', 'deleted':'0' };
        if (z.hasOwnProperty(result))
        {
          var div = el.parentNode;
          div.className = div.className.replace(/\bis-toggle-\d\b/, 'is-toggle-' + z[result]);
        }
      }
      
      function onMenuHide()
      {
        // clear icon focus state when dialog closes
        Dom.removeClass(el, 'active');
      }

      Dom.addClass(el, 'active');

      if (!this.oEditFlashcard)
      {
        Core.log("gotcha %o", data);
        this.oEditFlashcard = new App.Ui.EditFlashcardDialog(data.uri, Y.lang.JSON.parse(data.param), [this.elEditFlashcard, "tr", "br"], {
          events: {
            "onMenuResponse": onMenuResponse,
            "onMenuHide":     onMenuHide,
            scope:            this
          }
        });
      }
      else
      {
        this.oEditFlashcard.show();
      }

      return false;
    },

    /**
     * Auto-complete onchange callback, fires after user selects
     * something from the drop down list.
     * 
     * @param  string  text  String typed into the searchbox
     * 
     * @see    autocomplete.js
     */
    quicksearchOnChangeCallback:function(text)
    {
      if (text.length > 0)
      {
        // Lookup the first kanji if there is any kanji in the search string, ignore other characters
        // Regexp is equivalent of \p{InCJK_Unified_Ideographs}
        if (/([\u4e00-\u9fff])/.test(text))
        {
          text = RegExp.$1;
        }

        window.location.href = this.options.URL_SEARCH + '/' + this.anesthetizeThisBloodyUri(text);
        return true;
      }
    },

    /**
     * Auto-complete ENTER key callback.
     * 
     * @see    autocomplete.js
     */
    quicksearchEnterCallback:function(text)
    {
      this.quicksearchOnChangeCallback(text);
    },

    /**
     * Replaces problematic characters in the url which cause trouble
     * either with parsing the route (slash) or some kind of filter on the
     * web host's side which returns a 404 for urls with uncommon dot patterns
     * (eg. "/study/kanji/made in...").
     *
     * On the backend side, the dashes become wildcards.
     */
    anesthetizeThisBloodyUri: function(annoyingUri)
    {
      var s = annoyingUri.replace(/[\/\.]/g, '-');
      return encodeURIComponent(s);
    }
  };

}());

