/**
 * RtK Kanji flashcard review.
 *
 * Options:
 *
 *   fcr_options   Options to setup uiFlashcardReview
 *   end_url       Url to redirect to at the end of the review
 *
 */
// @ts-check

import $$, { DomJS, asHtmlElement, domGetById, hasClass } from "@lib/dom";
import AjaxDialog from "@old/ajaxdialog";
import DictLookupDialog from "@old/components/DictLookupDialog";
import EditFlashcardDialog from "@old/components/EditFlashcardDialog";
import EditStoryDialog from "@old/components/EditStoryDialog";
import FlashcardReview from "@app/review/FlashcardReview";

/** @typedef {{end_url: string, editstory_url: string}} TReviewProps */

export default class KanjiReview {
  /** @type {TReviewProps} */
  options;

  /** @type {FlashcardReview} */
  oReview;

  /** @type {DomJS<Element>}*/
  $elStats;
  /** @type {HTMLElement} */
  elProgressBar;

  /** @type {DictLookupDialog?} */
  dictDialog = null;

  /** @type {EditFlashcardDialog?} */
  oEditFlashcard = null;
  /** @type {TUcsId} */
  oEditFlashcardId = 0;

  /** @type {EditStoryDialog?} */
  editStoryDialog = null;

  /** @type {number[]} */
  deletedCards = [];

  /**
   *
   * @param {TReviewOptions} fcrOptions ... options for FlashcardReview instance
   * @param {TReviewProps} props ... props for Vue component (TBD refactor)
   */
  constructor(fcrOptions, props) {
    this.options = props;

    fcrOptions.events = {
      onBeginReview: this.onBeginReview,
      onEndReview: this.onEndReview,
      onFlashcardCreate: this.onFlashcardCreate,
      onFlashcardDestroy: this.onFlashcardDestroy,
      onFlashcardState: this.onFlashcardState,
      onFlashcardUndo: this.onFlashcardUndo,
      onAction: this.onAction,
    };
    fcrOptions.scope = this;

    this.oReview = new FlashcardReview(fcrOptions);

    this.oReview.addShortcutKey("f", "flip");
    this.oReview.addShortcutKey(" ", "flip");
    this.oReview.addShortcutKey(96, "flip"); // NUMPAD_0

    this.oReview.addShortcutKey("n", "no");
    this.oReview.addShortcutKey("g", "again");
    this.oReview.addShortcutKey("h", "hard");
    this.oReview.addShortcutKey("y", "yes");
    this.oReview.addShortcutKey("e", "easy");

    // added number keys to answer with just left hand
    this.oReview.addShortcutKey("1", "no");
    this.oReview.addShortcutKey("2", "hard");
    this.oReview.addShortcutKey("3", "yes");
    this.oReview.addShortcutKey("4", "easy");

    // same for numpad keys
    this.oReview.addShortcutKey(97, "no"); // NUMPAD_1
    this.oReview.addShortcutKey(98, "hard"); // NUMPAD_2
    this.oReview.addShortcutKey(99, "yes"); // NUMPDA_3
    this.oReview.addShortcutKey(100, "easy"); // NUMPAD_4

    this.oReview.addShortcutKey("u", "undo");
    this.oReview.addShortcutKey("s", "story");
    this.oReview.addShortcutKey("d", "dict");

    // skip flashcard (110 = comma)
    this.oReview.addShortcutKey("k", "skip");
    this.oReview.addShortcutKey(110, "skip"); // NUMPAD_DECIMAL

    // Disabled because it's next to (F)lip Card
    //this.oReview.addShortcutKey('d', 'delete');

    // flashcad container
    // this.elFlashcard = $$('.uiFcCard')[0];

    // stats panel
    this.$elStats = $$("#uiFcStats");
    this.elsCount = $$("#uiFcProgressBar .count");
    this.elProgressBar = asHtmlElement($$("#review-progress span")[0]);

    // answer stats
    this.elAnswerPass = this.$elStats.down(".JsPass")[0];
    this.elAnswerFail = this.$elStats.down(".JsFail")[0];
    this.countYes = 0;
    this.countNo = 0;

    this.countDeleted = 0;
    this.deletedCards = [];

    // end review div
    this.elFinish = asHtmlElement(this.$elStats.down(".JsFinish")[0]);
  }

  /**
   * Returns an option value
   *
   * @param {keyof TReviewProps} name
   */
  getOption(name) {
    return this.options[name];
  }

  // proxy which typecasts the card data, and *always* returns a valid card
  getFlashcardData() {
    return /**@type {TCardData}*/ (this.oReview.getFlashcardData());
  }

  onBeginReview() {
    //console.log('KanjiReview.onBeginReview()');
  }

  /**
   * Update the visible stats to the latest server hit,
   * and setup form data for redirection to the Review Summary page.
   *
   */
  onEndReview() {
    console.log("KanjiReview.onEndReview()");

    this.updateStatsPanel();

    var elFrm = /**@type {HTMLFormElement}*/ (domGetById("uiFcRedirectForm"));

    // set form data and redirect to summary with POST
    elFrm.method = "post";
    elFrm.action = this.getOption("end_url");
    /**@type{HTMLInputElement}*/ (elFrm.elements.namedItem("fc_pass")).value = "" + this.countYes;
    /**@type{HTMLInputElement}*/ (elFrm.elements.namedItem("fc_fail")).value = "" + this.countNo;
    /**@type{HTMLInputElement}*/ (elFrm.elements.namedItem("fc_deld")).value = this.deletedCards.join(",");
    elFrm.submit();
  }

  onFlashcardCreate() {
    console.log("KanjiReview.onFlashcardCreate()");

    // Show panels when first card is loaded
    if (this.oReview.getPosition() === 0) {
      this.$elStats.display();
    }

    // Show undo action if available
    $$("#JsBtnUndo").display(this.oReview.getNumUndos() > 0);

    this.updateStatsPanel();
  }

  /**
   * Hide buttons until next card shows up.
   *
   */
  onFlashcardDestroy() {
    $$("#uiFcButtons0").display(false);
    $$("#uiFcButtons1").display(false);
  }

  /**
   *
   * @param {TCardAnswer} oAnswer
   */
  onFlashcardUndo(oAnswer) {
    //  console.log('onFlashcardUndo(%o)', oAnswer);

    // correct the Yes / No totals
    this.updateAnswerStats(oAnswer.id, oAnswer.r, true);
  }

  /** @param {number} iState */
  onFlashcardState(iState) {
    console.log("onFlashcardState(%d)", iState);
    $$("#uiFcButtons0").display(iState === 0);
    $$("#uiFcButtons1").display(iState !== 0);
  }

  /**
   *
   * @param {string} sActionId cf. eventdispatcher
   * @param {Event} oEvent ...
   * @returns
   */
  onAction(sActionId, oEvent) {
    /** @type {TReviewRating=} */
    let cardRating;

    console.log("KanjiReview.onAction(%o)", sActionId);

    // help dialog
    if (sActionId === "help") {
      var dlg = new AjaxDialog("#JsFcHelpDlg", {
        useMarkup: true,
        context: ["JsBtnHelp", "tl", "bl", null, [0, 0]],
        skin: "rtk-skin-dlg",
        mobile: true,
        close: false,
      });
      dlg.show();

      return false;
    }

    // flashcard is loading
    if (!this.oReview.getFlashcard()) {
      return false;
    }

    if (sActionId === "story") {
      this.toggleEditStory();
      return false;
    }

    if (sActionId === "dict") {
      this.toggleDictDialog();
      return false;
    }

    switch (sActionId) {
      case "fcmenu":
        this.flashcardMenu();
        break;
      case "delete":
        this.answerCard(4);
        break;

      case "flip":
        if (oEvent.type === "click" && hasClass(asHtmlElement(oEvent.target), "JsKeywordLink")) {
          // pass through so the link functions
          return true;
        }
        if (this.oReview.getFlashcardState() === 0) {
          this.oReview.setFlashcardState(1);
        }
        break;

      case "undo":
        if (this.oReview.getNumUndos() > 0) {
          this.oReview.backward();
        }
        break;

      case "end":
        this.elFinish.style.display = "none";
        this.oReview.endReview(); // this will notify onEndReview()
        break;

      case "skip":
        this.answerCard(5);
        break;

      case "no":
        cardRating = 1;
        break;

      case "again":
        cardRating = "again";
        break;

      case "hard":
        cardRating = "h";
        break;

      case "yes":
        cardRating = 2;
        break;

      case "easy":
        cardRating = 3;
        break;
    }

    if (cardRating) {
      // "No" answer doesn't require flipping the card first (issue #163)
      if (sActionId === "no" || this.oReview.getFlashcardState() > 0) {
        this.answerCard(cardRating);
      }
    }

    return false;
  }

  toggleEditStory() {
    if (this.editStoryDialog && this.editStoryDialog.isVisible()) {
      this.editStoryDialog.hide();
    } else {
      const oCardData = this.getFlashcardData();

      if (!this.editStoryDialog) {
        // initialize Story Window and its position
        //var left = this.elFlashcard.offsetLeft + (this.elFlashcard.offsetWidth /2) - (520/2);
        //var top = this.elFlashcard.offsetTop + 61;
        this.editStoryDialog = new EditStoryDialog(this.getOption("editstory_url"), oCardData.id);
      } else {
        this.editStoryDialog.load(oCardData.id);
        this.editStoryDialog.show();
      }
    }
  }

  toggleDictDialog() {
    if (this.dictDialog && this.dictDialog.isVisible()) {
      this.dictDialog.hide();
    } else {
      const oCardData = this.getFlashcardData();
      const ucsId = oCardData.id;

      if (!this.dictDialog) {
        this.dictDialog = new DictLookupDialog();
      }

      this.dictDialog.show();
      this.dictDialog.load(ucsId);
    }
  }

  /**
   *
   * @param {TReviewRating} answer
   */
  answerCard(answer) {
    const oCardData = this.getFlashcardData();

    /** @type {TCardAnswer} */
    let oAnswer = { id: oCardData.id, r: answer };

    this.oReview.answerCard(oAnswer);
    this.updateAnswerStats(oAnswer.id, oAnswer.r, false);
    this.oReview.forward();
  }

  skipFlashcard() {
    this.answerCard(5);
  }

  /**
   * The little wrench icon that opens the menu contains:
   *
   *  data-param  {"review":1}    JSON data passed on the the menu ajax post (plus ucs id)
   *  data-uri                    Flashcard Edit Dialog ajax url
   *
   */
  flashcardMenu() {
    let el = /** @type {HTMLElement} */ ($$("#uiFcMenu")[0]);

    let data = /** @type {{uri: string, param: string}} */ (el.dataset);

    let oCardData = /** @type {TCardData}*/ (this.oReview.getFlashcardData());

    const onMenuHide = () => {
      // clear icon focus state when dialog closes
      el.classList.remove("active");
    };

    /** @param {string} menuid */
    const onMenuItem = (menuid) => {
      if (menuid === "confirm-delete") {
        // set flashcard answer that tells server to delete the card
        this.answerCard(4);
        return true;
      } else if (menuid === "skip") {
        this.skipFlashcard();
        return true;
      }

      // does not close dialog
      return false;
    };

    el.classList.add("active");

    // reload the edit flashcard menu when changed flashcard
    if (oCardData.id !== this.oEditFlashcardId) {
      this.oEditFlashcardId = oCardData.id;

      if (this.oEditFlashcard) {
        this.oEditFlashcard.destroy();
        this.oEditFlashcard = null;
      }
    }

    if (!this.oEditFlashcard) {
      var params = {
        ...JSON.parse(data.param),
        ...{ ucs: oCardData.id },
      };
      // console.log("zomg %o", params);return false;

      this.oEditFlashcard = new EditFlashcardDialog(data.uri, params, [el, "tr", "br"], {
        events: {
          onMenuHide: onMenuHide,
          onMenuItem: onMenuItem,
        },
      });
    } else {
      this.oEditFlashcard.show();
    }

    return false;
  }

  updateStatsPanel() {
    //  console.log('KanjiReview.updateStatsPanel()');
    const items = this.oReview.getItems();
    const num_items = items.length;
    const position = this.oReview.getPosition();

    // update review count
    this.elsCount[0].innerHTML = '' + Math.min(position + 1, num_items);
    this.elsCount[1].innerHTML = '' + num_items;

    // update progress bar
    var pct = position > 0 ? Math.ceil((position * 100) / num_items) : 0;
    pct = Math.min(pct, 100);
    this.elProgressBar.style.width = (pct > 0 ? pct : 0) + "%";
  }

  /**
   *
   * @param  {TUcsId} id ... the card's id (the kanji UCS code)
   * @param {TReviewRating} rating
   * @param  {boolean} undo
   */
  updateAnswerStats(id, rating, undo) {
    // cf. uiFlashcardReview.php const
    let yes = rating === 2 || rating === 3 ? 1 : 0;
    let no = rating === 1 || rating === "h" ? 1 : 0;
    let deld = rating === 4 ? 1 : 0;

    if (undo) {
      yes = -yes;
      no = -no;
      deld = -deld;
    }

    this.countYes += yes;
    this.countNo += no;
    this.elAnswerPass.innerHTML = "" + this.countYes;
    this.elAnswerFail.innerHTML = "" + this.countNo;

    if (deld !== 0) {
      this.updateDeletedCards(id, deld);
    }
  }

  /**
   *
   * @param {TUcsId} ucsId
   * @param {number} count
   */
  updateDeletedCards(ucsId, count) {
    this.countDeleted += count;

    if (count > 0) {
      this.deletedCards.push(ucsId);
    } else if (count < 0) {
      this.deletedCards.pop();
    }

    $$("#uiFcStDeld").display(this.countDeleted > 0);
    $$("#uiFcStDeld em")[0].innerHTML = "" + this.countDeleted;
    $$("#uiFcStDeldK span")[0].innerHTML = this.getDeletedCards();
  }

  getDeletedCards() {
    return "&#" + this.deletedCards.join(";&#") + ";";
  }

  /**
   * Sets buttons (children of element) to default state, or disabled state
   *
   * @param {HTMLElement} elParent
   * @param {boolean} bEnabled
   */
  setButtonState(elParent, bEnabled) {
    $$(".uiIBtn", elParent).each((el) => {
      el.classList.toggle("uiFcBtnDisabled", bEnabled);
    });
  }
}
