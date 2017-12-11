/**
 * Labs "alpha" (experimental features) -- Simple random test of vocabulary
 * 
 * @author  Fabrice Denis
 */
/*global alert, console, document, window, App, Core, YAHOO */


(function(){

  var Y = YAHOO,
      Dom = Y.util.Dom;

  App.LabsReview = 
  {
    initialize: function(options)
    {
      // set options
      this.options = options;
      
      options.fcr_options.events =
      {
        'onBeginReview':     this.onBeginReview,
        'onEndReview':       this.onEndReview,
        'onFlashcardCreate': this.onFlashcardCreate,
        'onFlashcardDestroy':this.onFlashcardDestroy,
        'onFlashcardState':  this.onFlashcardState,
        'onAction':          this.onAction,
        'scope':             this
      };

      this.oReview = new App.Ui.FlashcardReview(options.fcr_options);
      
      this.oReview.addShortcutKey('f', 'flip');
      this.oReview.addShortcutKey(' ', 'flip');
      this.oReview.addShortcutKey('b', 'back');

      // stats panel
      this.elStats = Dom.get('uiFcStats');
      this.elsCount = Dom.queryAll('#uiFcProgressBar', '.count'); //array
      this.elProgressBar = Dom.query('#review-progress', 'span');
    },
    
    /**
     * Returns an option value
     * 
     * @param  String   Option name
     */
    getOption: function(name)
    {
      return this.options[name];
    },
    
    onBeginReview: function()
    {
      //Core.log('labsReview.onBeginReview()');
    },

    /**
     * Update the visible stats to the latest server hit,
     * and setup form data for redirection to the Review Summary page.
     * 
     */
    onEndReview: function()
    {
      //Core.log('labsReview.onEndReview()');
      window.location.href = this.options.back_url;
    },

    onFlashcardCreate: function()
    {
      Core.log('labsReview.onFlashcardCreate()');

      // Show panels when first card is loaded
      if (this.oReview.getPosition() === 0)
      {
        this.elStats.style.display = 'block';
      }

      // Show undo action if available
      Dom.toggle('JsBtnBack', this.oReview.getPosition() > 0);

      this.updateStatsPanel();

      // set the google search url
      var searchTerm = this.oReview.getFlashcardData().compound;
      var searchUrl = 'http://www.google.co.jp/search?hl=ja&q=' + encodeURIComponent(searchTerm);
      Dom.get('search-google-jp').href = searchUrl;
    },

    /**
     * Hide buttons until next card shows up.
     * 
     */
    onFlashcardDestroy: function()
    {
      Dom.toggle('uiFcButtons0', false);
      Dom.toggle('uiFcButtons1', false);
    },

    onFlashcardState: function(iState)
    {
      Dom.toggle('uiFcButtons0', iState === 0);
      Dom.toggle('uiFcButtons1', iState !== 0);
    },

    onAction: function(sActionId, oEvent)
    {
      var cardAnswer = false;

      Core.log('App.LabsReview.onAction(%o)', arguments);

      // flashcard is loading or something..
      if (!this.oReview.getFlashcard())
      {
        return false;
      }

      switch (sActionId)
      {
        case 'back':
          if (this.oReview.getPosition() > 0)
          {
            this.oReview.backward();
          }
          break;
        
        case 'flip':
          if (this.oReview.getFlashcardState() === 0)
          {
            this.oReview.setFlashcardState(1);
          }
          else
          {
            this.oReview.forward();
          }
          break;

        case 'search-google-jp':
          break;
      }

      return false;
    },

    updateStatsPanel: function()
    {
    //  Core.log('labsReview.updateStatsPanel()');
      var items = this.oReview.getItems(),
      num_items = items.length,
      position  = this.oReview.getPosition();
      
      // update review count
      this.elsCount[0].innerHTML = Math.min(position + 1, num_items);
      this.elsCount[1].innerHTML = num_items;
      
      // update progress bar
      var pct = position > 0 ? Math.ceil(position * 100 / num_items) : 0;
      pct = Math.min(pct, 100);
      this.elProgressBar.style.width = (pct > 0 ? pct : 0) + '%';
    },

    /**
     * Sets buttons (children of element) to default state, or disabled state
     * 
     */
    setButtonState: function(elParent, bEnabled)
    {
      var buttons, i;
      
      buttons = Dom.getElementsByClassName('uiIBtn', 'a', elParent);

      for (i = 0; i < buttons.length; i++)
      {
        Dom.setClass(buttons[i], 'uiFcBtnDisabled', bEnabled);
      }
    }
  };

}());

