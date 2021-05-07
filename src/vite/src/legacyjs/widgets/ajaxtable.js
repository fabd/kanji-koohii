/**
 * AjaxTable uses AjaxPanel to allow sorting and paging through a custom list
 * or table structure (such as uiSelectTable php component), using ajax updates.
 *
 * Using AjaxPanel ensures that any parameters required to build the paged data,
 * can be encoded in a FORM and passed along when the user clicks the paging links.
 * 
 * How it works:
 * 
 *   By default, the html table component output by the backend contains sorting and paging
 *   variables as a query string in the links in the table heads, pager links, and also
 *   the rows per page selector. This allows sorting and paging via default GET requests
 *   without Javascript enabled.
 *   
 *   When javascript is enabled, AjaxTable intercepts clicks on the table heads and paging
 *   links. The query string found in the link's href attribute is routed as a POST request
 *   to an ajax controller. The ajax controller returns the updated table view.
 *
 *   AjaxTable looks for these classes:
 *
 *     JSTableSort     Class name set on the sortable table head links.
 *     JSPagerLink     Class name on the uiSelectPager links (only clickable links).
 *     JSFilterStd     Class name on the uiFilterStd links.
 *
 * Options:
 *
 *   errorDiv     Element id (string) of element to display (display:block) if TRON message
 *                returns an error.
 *
 */
import $$ from "@lib/koohii/dom";



  Core.Widgets.AjaxTable = Core.make();

  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  Core.Widgets.AjaxTable.prototype =
  {
    container: null,
    
    /**
     * 
     * @constructor
     *
     * @param {String|HTMLElement} container   Container element for the AjaxPanel. This is usually a DIV that wraps
     *                                         around the view template of the php table component (and pager, etc).
     * @param {Object} options                 See documentation.
     */
    init: function(container, options)
    {
      this.options = !!options ? options : {};

      this.container = Dom.get(container);
      this.oAjaxPanel = new Core.Ui.AjaxPanel(this.container, {
        events: {
          onResponse:   this.onResponse.bind(this),
          onSubmitForm: this.onSubmitForm.bind(this),
        }
      });

      // intercept clicks in the pager widget (rows per page, and paging links)
      this.evtDel = new EventDelegator(container, 'click');
      this.evtDel.on("JSTableSort", this.onRefreshTable, this);
      this.evtDel.on("JSPagerLink", this.onRefreshTable, this);
      this.evtDel.on("JSFilterStd", this.onRefreshTable, this);

      // clicks in the pager widget will be ignored while waiting for a response
      this.ajaxInProgress = false;
    },
  
    destroy: function()
    {
      this.evtDel.destroy();
      this.oAjaxPanel.destroy();
      this.oAjaxPanel = null;
    },
  
    /**
     * Send the query string from the clicked link as an ajax request.
     * 
     * Query string should always start with a "?" (with or without url)
     * 
     * @note  EventDelegator handler.
     */
    onRefreshTable: function(ev, el)
    {
      var query, pos, params;

      if (this.ajaxInProgress)
      {
        console.log('AjaxTable::onRefreshTable() ajax in progress, ignoring clicks');
        return false;
      }

      if (!this.ajaxInProgress)
      {
        this.setPagingToDisabledStyle(true);
      }

      if ((query = Dom.getAttribute(el, "href")) && (pos = query.indexOf('?')) >= 0)
      {
        params = query.substr(pos + 1);
        this.oAjaxPanel.send(params);

        this.ajaxInProgress = true;

        return false;
      }

      return true;
    },

    /**
     * Sets the pager container with the paging links to a "disabled" style
     * to indicate it is not available while the ajax connection is under progress.
     */
    setPagingToDisabledStyle: function(bDisable)
    {
      var opacityValue = bDisable ? '0.3' : '1' /* default */;
      if ((this.elPagerDiv = $$('.uiPagerDiv', this.container)[0]))
      {
        $$(this.elPagerDiv).css({ opacity: opacityValue });
      }
    },

    /**
     * Listener for AjaxPanel's onResponse(), gets called before content replace.
     *
     * Display an error message to the client based on the TRON status, without
     * replacing the content.
     *
     * @param {Object} t    TRON instance or null
     */
    onResponse: function(t)
    {
      console.log('AjaxTable::onResponse(%o)', t);

      if (t === null) {
        return;
      }

      this.ajaxInProgress = false;

      if (t.hasErrors())
      {
        var sMsg = t.getErrors().join('\n'),
            el   = Dom.get(this.options.errorDiv);

        if (el)
        {
          el.innerHTML = sMsg; 
          $$(el).css({ display: 'block' });
        }
        else
        {
          alert(sMsg);
        }

        // content was not replaced, so enable paging links
        this.setPagingToDisabledStyle(false);
      }
    },
  
    /**
     * This is a placeholder to prevent default FORM submission of AjaxPanel.
     * 
     */
    onSubmitForm: function(ev)
    {
      return false;
    }
  };


