/**
 * RtK Kanji flashcard review.
 * 
 * Options:
 * 
 *   fcr_options   Options to setup uiFlashcardReview
 *   end_url       Url to redirect to at the end of the review
 *
 * @author   Fabrice Denis
 * @package  RevTK
 */
/*global alert, console, document, window, App, Core, YAHOO */


/* =require from "%WEB%" */
/* =require "/revtk/bundles/flashcardreview-1.0.juicy.js" */
/* =require "/revtk/components/EditStoryComponent.js" */
/* =require "/revtk/components/EditStoryDialog.js" */
/* =require "/revtk/components/EditKeywordDialog.js" */
/* =require "/revtk/components/EditFlashcardDialog.js" */
/* =require "/revtk/components/DictLookupDialog.js" */


(function(){

  var Y = YAHOO,
      Dom = Y.util.Dom;

  App.KanjiReview = 
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
        'onFlashcardUndo':   this.onFlashcardUndo,
        'onAction':          this.onAction,
        'scope':             this
      };

      this.oReview = new App.Ui.FlashcardReview(options.fcr_options);

      this.oReview.addShortcutKey('f', 'flip');
      this.oReview.addShortcutKey(' ', 'flip');
      this.oReview.addShortcutKey('n', 'no');
      this.oReview.addShortcutKey('h', 'hard');
      this.oReview.addShortcutKey('y', 'yes');
      this.oReview.addShortcutKey('e', 'easy');
      this.oReview.addShortcutKey('u', 'undo');
      this.oReview.addShortcutKey('s', 'story');
      this.oReview.addShortcutKey('d', 'dict');

      // skip flashcard (110 = comma)
      this.oReview.addShortcutKey('k', 'skip');
      this.oReview.addShortcutKey(110, 'skip');
      
      // Disabled because it's next to (F)lip Card
      //this.oReview.addShortcutKey('d', 'delete');

      // added number keys to answer with just left hand
      this.oReview.addShortcutKey('1', 'no');
      this.oReview.addShortcutKey('2', 'yes');
      this.oReview.addShortcutKey('3', 'easy');
      // same for numpad keys
      this.oReview.addShortcutKey(96, 'flip');
      this.oReview.addShortcutKey(97, 'no');
      this.oReview.addShortcutKey(98, 'yes');
      this.oReview.addShortcutKey(99, 'easy');

      // flashcad container
      this.elFlashcard = Dom.down(document.body, 'uiFcCard', 'div');

      // stats panel
      this.elStats = Dom.get('uiFcStats');
      this.elsCount = Dom.queryAll('#uiFcProgressBar', '.count'); //array
      this.elProgressBar = Dom.query('#review-progress', 'span');

      // answer stats
      this.elAnswerPass = Dom.down(this.elStats, 'JsPass');
      this.elAnswerFail = Dom.down(this.elStats, 'JsFail');
      this.countYes = 0;
      this.countNo  = 0;

      this.countDeleted = 0;
      this.deletedCards = [];
      
      // end review div
      this.elFinish = Dom.down(this.elStats, 'JsFinish');
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
      //Core.log('App.KanjiReview.onBeginReview()');
    },

    /**
     * Update the visible stats to the latest server hit,
     * and setup form data for redirection to the Review Summary page.
     * 
     */
    onEndReview: function()
    {
      Core.log('App.KanjiReview.onEndReview()');
      
      this.updateStatsPanel();

      // set form data and redirect to summary with POST
      var elFrm = Dom.get('uiFcRedirectForm');
      elFrm.method = 'post';
      elFrm.action = this.getOption('end_url');
      elFrm.elements['fc_pass'].value = this.countYes;
      elFrm.elements['fc_fail'].value = this.countNo;
      elFrm.elements['fc_deld'].value = this.deletedCards.join(',');
      elFrm.submit();
    },

    onFlashcardCreate: function()
    {
      //Core.log('App.KanjiReview.onFlashcardCreate()');

      // Show panels when first card is loaded
      if (this.oReview.getPosition() === 0)
      {
        this.elStats.style.display = 'block';
      }

      // Show undo action if available
      Dom.toggle('JsBtnUndo', this.oReview.getNumUndos() > 0);

      this.updateStatsPanel();
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

    onFlashcardUndo: function(oAnswer)
    {
    //  Core.log('onFlashcardUndo(%o)', oAnswer);
      
      // correct the Yes / No totals
      this.updateAnswerStats(oAnswer, true);
    },
      
    onFlashcardState: function(iState)
    {
      Dom.toggle('uiFcButtons0', iState === 0);
      Dom.toggle('uiFcButtons1', iState !== 0);
    },

    onAction: function(sActionId, oEvent)
    {
      var oCardData, cardAnswer = false;

      Core.log('App.KanjiReview.onAction(%o)', arguments);

      // help dialog
      if (sActionId === 'help')
      {
        var dlg = new Core.Ui.AjaxDialog('JsFcHelpDlg', {
          useMarkup: true,
          context:   ["JsBtnHelp", "tl", "bl", null, [0, 0]],
          skin:      "rtk-skin-dlg",
          mobile:    true,
          close:     false
        });
        dlg.show();

        return false;
      }

      // flashcard is loading
      if (!this.oReview.getFlashcard()) {
        return false;
      }

      if (sActionId==='story')
      {
        if (this.editStoryDialog && this.editStoryDialog.isVisible())
        {
          this.editStoryDialog.hide();
        }
        else
        {
          oCardData = this.oReview.getFlashcardData();
          
          if (!this.editStoryDialog)
          {
            // initialize Story Window and its position
            //var left = this.elFlashcard.offsetLeft + (this.elFlashcard.offsetWidth /2) - (520/2);
            //var top = this.elFlashcard.offsetTop + 61;
            this.editStoryDialog = new App.Ui.EditStoryDialog(this.getOption('editstory_url'), oCardData.id);
          }
          else
          {
            this.editStoryDialog.load(oCardData.id);
            this.editStoryDialog.show();
          }
        }
        return false;
      }

      if (sActionId === 'dict')
      {
        if (this.dictDialog && this.dictDialog.isVisible())
        {
          this.dictDialog.hide();
        }
        else
        {
          oCardData = this.oReview.getFlashcardData();

          if (!this.dictDialog)
          {
            this.dictDialog = new App.Ui.DictLookupDialog(this.getOption('dictlookup_url'), oCardData.id);
          }
          else
          {
            this.dictDialog.load(oCardData.id);
            this.dictDialog.show();
          }
        }
      }

      switch (sActionId)
      {
        case 'fcmenu':
          this.flashcardMenu();
          break;
        case 'delete':
          this.answerCard(4);
          break;

        case 'flip':
          if (oEvent.type === 'click' && Dom.hasClass(oEvent.target, 'JsKeywordLink'))
          {
            // pass through so the link functions
            return true;
          }

          if (this.oReview.getFlashcardState() === 0) 
          {
            this.oReview.setFlashcardState(1);
          }
          break;
          
        case 'undo':
          if (this.oReview.getNumUndos() > 0) 
          {
            this.oReview.backward();
          }
          break;
        
        case 'end':
          this.elFinish.style.display = 'none';
          this.oReview.endReview();   // this will notify onEndReview()
          break;
 
        case 'skip':
          this.answerCard(5);
          break;

        case 'no':
          cardAnswer = 1;
          break;

        case 'hard':
          cardAnswer = 'h';
          break;

        case 'yes':
          cardAnswer = 2;
          break;

        case 'easy':
          cardAnswer = 3;
          break;
      }

      if (cardAnswer)
      {
        // check if flashcard is flipped yet
        if (this.oReview.getFlashcardState() > 0)
        {
          this.answerCard(cardAnswer);
        }
      }

      return false;
    },

    /**
     *
     * @param  int   answer   1-3 (No/Yes/Easy) h (Hard), 4 (Delete), 5 (Skip)
     */
    answerCard: function(answer)
    {
      var oCardData = this.oReview.getFlashcardData(),
          oAnswer   = { id: oCardData.id, r: answer };

      this.oReview.answerCard(oAnswer);
      this.updateAnswerStats(oAnswer, false);
      this.oReview.forward();
    },

    skipFlashcard: function()
    {
      this.answerCard(5);
    },

    /**
     * The little wrench icon that opens the menu contains:
     *
     *  data-param  {"review":1}    JSON data passed on the the menu ajax post (plus ucs id)
     *  data-uri                    Flashcard Edit Dialog ajax url
     *  
     */
    flashcardMenu: function()
    {
      var el        = Dom.get('uiFcMenu'),
          data      = Dom.getDataset(el),
          oCardData = this.oReview.getFlashcardData();

      function onMenuHide()
      {
        // clear icon focus state when dialog closes
        Dom.removeClass(el, 'active');
      }

      function onMenuItem(menuid)
      {
        if (menuid === 'confirm-delete')
        {
          // set flashcard answer that tells server to delete the card
          this.answerCard(4);
          return true;
        }
        else if (menuid === 'skip')
        {
          this.skipFlashcard();
          return true;
        }

        // does not close dialog
        return false;
      }

      Dom.addClass(el, 'active');

      // reload the edit flashcard menu when changed flashcard
      if (oCardData.id !== this.oEditFlashcardId)
      {
        this.oEditFlashcardId = oCardData.id;

        if (this.oEditFlashcard)
        {
          this.oEditFlashcard.destroy();
          this.oEditFlashcard = null;
        }
      }

      if (!this.oEditFlashcard)
      {
        var params = Y.lang.merge(Y.lang.JSON.parse(data.param), { ucs: oCardData.id });
        //Core.log("zomg %o", params);return false;
        
        this.oEditFlashcard = new App.Ui.EditFlashcardDialog(data.uri, params, [el, "tr", "br"], {
          events: {
            "onMenuHide":  onMenuHide,
            "onMenuItem":  onMenuItem
          },
          scope: this
        });
      }
      else
      {
        this.oEditFlashcard.show();
      }

      return false;
    },

    updateStatsPanel: function()
    {
    //  Core.log('App.KanjiReview.updateStatsPanel()');
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
     *
     * @param  {Object}  answer  { id: <ucs_id>, r: <answer code> }
     * @param  {Boolean} undo
     */
    updateAnswerStats: function(answer, undo)
    {
      // cf. uiFlashcardReview.php const
      var yes  = (answer.r === 2 || answer.r ===3) ? 1 : 0,
          no   = (answer.r === 1 || answer.r === 'h') ? 1 : 0,
          deld = answer.r===4 ? 1 : 0;

      if (undo) {
        yes  = -yes;
        no   = -no;
        deld = -deld;
      }

      this.countYes += yes;
      this.countNo  += no;
      this.elAnswerPass.innerHTML = this.countYes;
      this.elAnswerFail.innerHTML = this.countNo;

      if (deld !== 0)
      {
        this.updateDeletedCards(answer.id, deld);
      }
    },

    updateDeletedCards: function(ucsId, count)
    {
      this.countDeleted += count;

      if (count > 0)
      {
        this.deletedCards.push(ucsId);
      }
      else if (count < 0)
      {
        this.deletedCards.pop();
      }

      Dom.toggle('uiFcStDeld', this.countDeleted > 0);

      Dom.get('uiFcStDeld').getElementsByTagName('em')[0].innerHTML = this.countDeleted;

      Dom.getFirstChild('uiFcStDeldK').innerHTML = this.getDeletedCards();
    },

    getDeletedCards: function()
    {
      return '&#' + this.deletedCards.join(';&#') + ';';
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
  
  // GreaseMonkey "kanji to keyword" compatibility (2011-02-21)
  window.rkKanjiReview = App.KanjiReview;

}());

