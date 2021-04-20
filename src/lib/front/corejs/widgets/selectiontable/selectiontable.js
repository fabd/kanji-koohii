/**
 * Extends an AjaxTable widget with the ability to select rows.
 *
 * - Select rows by clicking the check box, or clicking anywhere inside the row.
 * - Select/deselect all rows with the checkbox in the table head.
 *
 * Methods:
 *   getPostData()     Return serialized input hidden data that the backend can
 *                     use to refresh the selection state.
 *
 * @see       See related backend class uiSelectionState.php
 * 
 * @author    Fabrice Denis
 */
/*global App, Core, YAHOO */

/* =require from "%CORE%" */
/* =require "/widgets/ajaxtable/ajaxtable.js" */

(function() {

  var Y = YAHOO,
      $$ = Koohii.Dom,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  Core.Widgets.SelectionTable = Core.make();
  
  Core.Widgets.SelectionTable.prototype =
  {
    selection: {},

    /**
     * @constructor
     * 
     * @param {String|HTMLElement} container   Container element or string id.
     */
    init: function(container)
    {
      this.selection = {};

      this.oAjaxTable = new Core.Widgets.AjaxTable(container);
      this.oAjaxTable.evtDel.on("checkbox", this.onCheckBox, this);
      this.oAjaxTable.evtDel.on("chkAll", this.onCheckAll, this);
      this.oAjaxTable.evtDel.onDefault(this.onClick, this);
    },
    
    destroy: function()
    {
      this.oAjaxTable.destroy();
      this.oAjaxTable = null;
    },

    /**
     * Returns serialized form data for the hidden inputs that store
     * the state of selected rows.
     *
     * @see  uiSelectionState.php
     */
    getPostData: function()
    {
      var inputs = Dom.getElementsByClassName("checkbox", "input", this.getTable().tBodies[0]),
          data   = {},
          i;
      for (i = 0; i < inputs.length; i++)
      {
        var input = inputs[i].parentNode.getElementsByTagName("input")[0];
        console.assert(!!input, "getPostData() invalid markup?");
        data[input.name] = input.value;
      }

      return data;
    },

    getTable: function()
    {
      return this.oAjaxTable.container.getElementsByTagName("table")[0];
    },
    
    onCheckBox: function(ev, el)
    {
      var row    = Dom.getAncestorByTagName(el, "tr"),
          inputs = el.parentNode.getElementsByTagName("input");

      this.setSelection(row, inputs[0], el.checked);

      // pass through otherwise the checkbox won't check
      return true;
    },
    
    onCheckAll: function(ev, el)
    {
      var i,
          check = el.checked,
          rows  = this.getTable().tBodies[0].getElementsByTagName("tr");
     
      for (i = 0; i < rows.length; i++)
      {
        var tr = rows[i],
            inputs = tr.getElementsByTagName("input");
        if (inputs[1].checked !== check) {
          inputs[1].checked = check;
          this.setSelection(tr, inputs[0], check);
        }
      }
      
      return true;
    },

    onClick: function(ev)
    {
      var row, check, el = Event.getTarget(ev);

      // watch for already handled checkbox clicks
      if (el.tagName.toLowerCase() === 'input')
      {
        return true;
      }

      // if clicked in a row, select it
      if ((row = Dom.getAncestorByTagName(el, "tr")))
      {
        if ((check = $$('.checkbox', row)[0]))
        {
          check.click();
          Event.stopEvent(ev);
        }
      }

      return true;
    },
    
    setSelection: function(row, input, check)
    {
      // set value
      input.value = check ? "1" : "0";
      // set highlight
      row.classList.toggle("selected", check);
    }
  };

}());
