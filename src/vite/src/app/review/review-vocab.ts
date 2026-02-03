import $$, { DomJS, hasClass } from "@lib/dom";
import FlashcardReview from "@app/review/FlashcardReview";
import ReviewPage from "@app/review/ReviewPage";

export default class VocabReview {
  options: TVocabReviewProps;

  oReview: FlashcardReview;

  $elStats: DomJS<Element>;
  elProgressBar: HTMLElement;
  elsCount: DomJS<Element>;
  reviewPage: ReviewPage;

  /**
   *
   * @param fcrOptions ... options for FlashcardReview instance
   * @param props ... props for Vue component (TBD refactor)
   */
  constructor(fcrOptions: TReviewOptions, props: TVocabReviewProps) {
    // set options
    this.options = props;

    fcrOptions.events = {
      onEndReview: this.onEndReview.bind(this),
      onFlashcardCreate: this.onFlashcardCreate.bind(this),
      onFlashcardDestroy: this.onFlashcardDestroy.bind(this),
      onFlashcardState: this.onFlashcardState.bind(this),
    };

    this.oReview = new FlashcardReview(fcrOptions);

    this.reviewPage = new ReviewPage(this.onAction.bind(this));
    this.reviewPage.addShortcutKey("f", "flip");
    this.reviewPage.addShortcutKey(" ", "flip");
    this.reviewPage.addShortcutKey("b", "back");

    // stats panel
    this.$elStats = $$("#uiFcStats");
    this.elsCount = $$("#uiFcProgressBar .count"); //array
    this.elProgressBar = $$("#review-progress span")[0] as HTMLElement;
  }

  /**
   * Returns an option value
   *
   */
  getOption(name: keyof TVocabReviewProps) {
    return this.options[name];
  }

  /**
   * proxy which *always* returns a valid card
   *
   */
  getFlashcardData(): TVocabCardData {
    return this.oReview.getFlashcardData() as unknown as TVocabCardData;
  }

  /**
   * Update the visible stats to the latest server hit,
   * and setup form data for redirection to the Review Summary page.
   *
   */
  onEndReview() {
    //console.log('VocabReview.onEndReview()');
    window.location.href = this.getOption("back_url");
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
    const searchTerm = this.getFlashcardData().compound;
    const searchUrl =
      "http://www.google.co.jp/search?hl=ja&q=" +
      encodeURIComponent(searchTerm);
    const elLink = $$("#search-google-jp")[0] as HTMLAnchorElement;
    elLink.href = searchUrl;
  }

  /**
   * Hide buttons until next card shows up.
   *
   */
  onFlashcardDestroy() {
    $$("#uiFcButtons0").display(false);
    $$("#uiFcButtons1").display(false);
  }

  onFlashcardState(iState: number) {
    $$("#uiFcButtons0").display(iState === 0);
    $$("#uiFcButtons1").display(iState !== 0);
  }

  /**
   *
   * @param sActionId cf. eventdispatcher
   * @param oEvent ...
   */
  onAction(sActionId: string, oEvent: Event) {
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
        if (
          oEvent.type === "click" &&
          hasClass(oEvent.target as Element, "JsLink")
        ) {
          // pass through so the link functions
          return true;
        }
        if (this.oReview.getFlashcardState() === 0) {
          this.oReview.setFlashcardState(1);
        } else {
          this.oReview.forward();
        }
        break;
    }

    return false;
  }

  updateStatsPanel() {
    //  console.log('VocabReview.updateStatsPanel()');
    const items = this.oReview.getItems(),
      num_items = items.length,
      position = this.oReview.getPosition();

    // update review count
    this.elsCount[0]!.innerHTML = "" + Math.min(position + 1, num_items);
    this.elsCount[1]!.innerHTML = "" + num_items;

    // update progress bar
    let pct = position > 0 ? Math.ceil((position * 100) / num_items) : 0;
    pct = Math.min(pct, 100);
    this.elProgressBar.style.width = (pct > 0 ? pct : 0) + "%";
  }

  /**
   * Sets buttons (children of element) to default state, or disabled state
   *
   */
  setButtonState(elParent: HTMLElement, bEnabled: boolean) {
    $$(".uiIBtn", elParent).each((el) => {
      el.classList.toggle("uiFcBtnDisabled", bEnabled);
    });
  }
}
