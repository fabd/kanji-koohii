// FIXME: refactor into a single class for srs/free/vocab modes
// @ts-check

import $$, { DomJS, asHtmlElement, domGetById, hasClass } from "@lib/dom";
import FlashcardReview from "@app/review/FlashcardReview";

export default class VocabReview {
  /** @type {Dictionary} */
  options = {};

  /** @type {FlashcardReview} */
  oReview;

  /** @type {DomJS<Element>}*/
  $elStats;
  /** @type {HTMLElement} */
  elProgressBar;

  /**
   *
   * @param {Dictionary} options
   */
  constructor(options) {
    // set options
    this.options = options;

    options.fcr_options.events = {
      onBeginReview: this.onBeginReview,
      onEndReview: this.onEndReview,
      onFlashcardCreate: this.onFlashcardCreate,
      onFlashcardDestroy: this.onFlashcardDestroy,
      onFlashcardState: this.onFlashcardState,
      onAction: this.onAction,
      scope: this,
    };

    this.oReview = new FlashcardReview(options.fcr_options);

    this.oReview.addShortcutKey("f", "flip");
    this.oReview.addShortcutKey(" ", "flip");
    this.oReview.addShortcutKey("b", "back");

    // stats panel
    this.$elStats = $$("#uiFcStats");
    this.elsCount = $$("#uiFcProgressBar .count"); //array
    this.elProgressBar = asHtmlElement($$("#review-progress span")[0]);
  }

  /**
   * Returns an option value
   *
   * @param {string} name
   */
  getOption(name) {
    return this.options[name];
  }

  // proxy which *always* returns a valid card
  getFlashcardData() {
    return /**@type {TVocabCardData}*/ (this.oReview.getFlashcardData());
  }

  onBeginReview() {
    //console.log('VocabReview.onBeginReview()');
  }

  /**
   * Update the visible stats to the latest server hit,
   * and setup form data for redirection to the Review Summary page.
   *
   */
  onEndReview() {
    //console.log('VocabReview.onEndReview()');
    window.location.href = this.options.back_url;
  }

  onFlashcardCreate() {
    console.log("VocabReview.onFlashcardCreate()");

    // Show panels when first card is loaded
    if (this.oReview.getPosition() === 0) {
      this.$elStats.display();
    }

    // Show undo action if available
    $$("#JsBtnBack").display(this.oReview.getPosition() > 0);

    this.updateStatsPanel();

    // set the google search url
    let searchTerm = this.getFlashcardData().compound;
    let searchUrl = "http://www.google.co.jp/search?hl=ja&q=" + encodeURIComponent(searchTerm);
    /**@type{HTMLAnchorElement}*/($$("#search-google-jp")[0]).href = searchUrl;
  }

  /**
   * Hide buttons until next card shows up.
   *
   */
  onFlashcardDestroy() {
    $$("#uiFcButtons0").display(false);
    $$("#uiFcButtons1").display(false);
  }

  /** @param {number} iState */
  onFlashcardState(iState) {
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
    console.log("VocabReview.onAction(%o)", sActionId);

    // flashcard is loading or something..
    if (!this.oReview.getFlashcard()) {
      return false;
    }

    switch (sActionId) {
      case "back":
        if (this.oReview.getPosition() > 0) {
          this.oReview.backward();
        }
        break;

      case "flip":
        if (this.oReview.getFlashcardState() === 0) {
          this.oReview.setFlashcardState(1);
        } else {
          this.oReview.forward();
        }
        break;

      case "search-google-jp":
        break;
    }

    return false;
  }

  updateStatsPanel() {
    //  console.log('VocabReview.updateStatsPanel()');
    var items = this.oReview.getItems(),
      num_items = items.length,
      position = this.oReview.getPosition();

    // update review count
    this.elsCount[0].innerHTML = "" + Math.min(position + 1, num_items);
    this.elsCount[1].innerHTML = "" + num_items;

    // update progress bar
    var pct = position > 0 ? Math.ceil((position * 100) / num_items) : 0;
    pct = Math.min(pct, 100);
    this.elProgressBar.style.width = (pct > 0 ? pct : 0) + "%";
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
