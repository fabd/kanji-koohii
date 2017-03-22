/**
 * EditStoryComponent client side.
 * 
 * Methods:
 * 
 *   isEdit()     Returns true if currently in edit mode.
 *   doCancel()   Force return to view mode and cancel changes.
 * 
 */
/*global YAHOO, window, alert, console, document, Core, App */

(function(){

  App.Ui.EditStoryComponent = Core.make();

  var Y = YAHOO,
      Dom = Y.util.Dom,
      Event = Y.util.Event;

  App.Ui.EditStoryComponent.prototype =
  {
    /**
     *
     * @param {HTMLElement|String}  Container element.
     */
    init:function(container)
    {
      Core.log('EditStoryComponent.initialize()');

      this.elContainer = Dom.get(container);

      this.evtCache = new Core.Ui.EventCache();

      // set defaults
      this.bEdit = false;
//      this.sBackupStory = '';

      var el = Dom.get('sv-textarea');
      this.evtCache.addEvent(el, 'mouseover', Core.bind(this.onHover, this));
      this.evtCache.addEvent(el, 'mouseout', Core.bind(this.onHover, this));

      // event delegation
      this.evtDel = new Core.Ui.EventDelegator(this.elContainer, ["click"]);
      this.evtDel.on('JSEditKeyword', this.onKeyword, this);
      this.evtDel.onId('sv-textarea', this.onEdit, this);
      this.evtDel.onId('storyedit_cancel', this.onCancel, this);
    },
    
    destroy:function()
    {
      if (this.oEditKeyword)
      {
        this.oEditKeyword.destroy();
        this.oEditKeyword = null;
      }

      this.evtCache.destroy();
      this.evtCache = null;
      this.evtDel.destroy();
      this.evtDel = null;
    },

    /**
     * EventDelegator event.
     *
     */
    onKeyword: function(e, el)
    {
      var data, options, that = this;

      function callback(keyword)
      {
        Core.log('EditKeywordComponent callback');
        el.innerHTML = keyword;
        // force reload
        that.oEditKeyword.destroy();
        that.oEditKeyword = null;
      }

      if (!this.oEditKeyword)
      {
        data = Dom.getDataset(el);
        options = { context: ["my-story", "tr", "tr", null, [-6, 6]] };
        this.oEditKeyword = new App.Ui.EditKeywordComponent(data.url, options, callback);
      }
      else
      {
        this.oEditKeyword.show();
      }
    },
    
    /**
     * Enter edit mode.
     * 
     */
    onEdit:function(oEvent)
    {
      this.editStory();
    },

    /**
     * Returns true if currently in edit mode.
     * 
     * @return boolean
     */
    isEdit:function()
    {
      return this.bEdit;
    },

    /**
     * Edit Story or Edit a copy of another user's story.
     * 
     * @param {Object} sCopyStory   The "copy" story feature will set this to the copied story text.
     */  
    editStory:function(sCopyStory)  
    {
//      this.sBackupStory = Dom.get('frmStory').value;

      // edit a new story, cancel will restore the previous one
      if (sCopyStory) {
        Dom.get('frmStory').value = sCopyStory;
      }

      Dom.get('storyview').style.display = 'none';
      Dom.get('storyedit').style.display = 'block';
      
      var  elTextArea = Dom.get('frmStory');
      this.setCaretToEnd(elTextArea);
      
      this.bEdit = true;
    },

    onHover:function(e)
    {
      var type = e.type, el = Event.getTarget(e);
      Dom.setClass(el, 'hover', type === 'mouseover');
    },
    
    onCancel:function(oEvent)
    {
      this.doCancel();
    },

    /**
     * Cancel any changes and switch back to view mode
     * 
     */
    doCancel:function()
    {
      Dom.get('storyedit').style.display = 'none';
//      Dom.get('frmStory').value = this.sBackupStory;
      Dom.get('storyview').style.display = 'block';
      
      this.bEdit = false;
    },
    
    /**
     * Cross-browser move caret to end of input field
     */
    setCaretToEnd:function(element)
    {
      if (element.createTextRange) {
        var range = element.createTextRange();
        range.collapse(false);
        range.select();
      }
      else if (element.setSelectionRange) {
        element.focus();
        var length = element.value.length;
        element.setSelectionRange(length, length);
      }
    }
  };

}());

