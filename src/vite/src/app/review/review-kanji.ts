/**
 * RtK Kanji flashcard review.
 *
 * Options:
 *
 *   fcr_options   Options to setup FlashcardReview
 *   end_url       Url to redirect to at the end of the review
 *
 */

import $$, { hasClass } from "@lib/dom";
import KoDialog from "@/vue/KoDialog";
import DictLookupDialog from "@old/components/DictLookupDialog";
import EditFlashcardDialog from "@old/components/EditFlashcardDialog";
import EditStoryDialog from "@old/components/EditStoryDialog";
import FlashcardReview, { FCRATE } from "@app/review/FlashcardReview";
import ReviewPage from "@app/review/ReviewPage";
import eventBus from "@/lib/EventBus";

export default class KanjiReview {
  options: TKanjiReviewProps;

  oReview: FlashcardReview;

  elProgressBar: HTMLElement;

  dictDialog: DictLookupDialog | null = null;
  oEditFlashcard: EditFlashcardDialog | null = null;
  oEditFlashcardId: TUcsId = 0;
  editStoryDialog: EditStoryDialog | null = null;
  editStoryDialogId: TUcsId = 0;

  deletedCards: number[] = [];

  elAnswerPass: Element;
  elAnswerFail: Element;
  countYes: number;
  countNo: number;
  countDeleted: number;

  elStats: HTMLElement;
  elFinish: HTMLElement;
  reviewPage: ReviewPage;

  /**
   *
   * @param fcrOptions ... options for FlashcardReview instance
   * @param props ... props for Vue component (TODO)
   */
  constructor(fcrOptions: TReviewOptions, props: TKanjiReviewProps) {
    this.options = props;

    fcrOptions.events = {
      onEndReview: this.onEndReview.bind(this),
      onFlashcardCreate: this.onFlashcardCreate.bind(this),
      onFlashcardDestroy: this.onFlashcardDestroy.bind(this),
      onFlashcardState: this.onFlashcardState.bind(this),
      onFlashcardUndo: this.onFlashcardUndo.bind(this),
    };

    this.oReview = new FlashcardReview(fcrOptions);

    this.reviewPage = new ReviewPage(this.onAction.bind(this));

    this.reviewPage.addShortcutKey("f", "flip");
    this.reviewPage.addShortcutKey(" ", "flip");
    this.reviewPage.addShortcutKey(96, "flip"); // NUMPAD_0

    this.reviewPage.addShortcutKey("n", "no");
    this.reviewPage.addShortcutKey("a", "again");
    this.reviewPage.addShortcutKey("y", "yes");
    if (!this.options.freemode) {
      this.reviewPage.addShortcutKey("h", "hard");
      this.reviewPage.addShortcutKey("e", "easy");
    }

    // added number keys to answer with just left hand
    this.reviewPage.addShortcutKey("1", "no");
    this.reviewPage.addShortcutKey("3", "yes");
    if (!this.options.freemode) {
      this.reviewPage.addShortcutKey("2", "hard");
      this.reviewPage.addShortcutKey("4", "easy");
    }

    // same for numpad keys
    this.reviewPage.addShortcutKey(97, "no"); // NUMPAD_1
    this.reviewPage.addShortcutKey(98, "hard"); // NUMPAD_2
    this.reviewPage.addShortcutKey(99, "yes"); // NUMPDA_3
    this.reviewPage.addShortcutKey(100, "easy"); // NUMPAD_4

    this.reviewPage.addShortcutKey("u", "undo");
    this.reviewPage.addShortcutKey("s", "story");
    this.reviewPage.addShortcutKey("d", "dict");

    // skip flashcard (110 = comma)
    this.reviewPage.addShortcutKey("k", "skip");
    this.reviewPage.addShortcutKey(110, "skip"); // NUMPAD_DECIMAL
    
    // event listeners for the flashcard menu
    eventBus.connect("kk.review.skip", this.onFlashcardSkip.bind(this));
    eventBus.connect("kk.review.delete", this.onFlashcardDelete.bind(this));

    // flashcad container
    // this.elFlashcard = $$('.uiFcCard')[0];

    // stats panel
    this.elStats = $$<HTMLElement>(".JSFcStats")[0]!;
    this.elProgressBar = $$<HTMLElement>("#review-progress span")[0]!;

    // answer stats
    this.elAnswerPass = $$(".JSCountPass")[0]!;
    this.elAnswerFail = $$(".JSCountFail")[0]!;
    this.countYes = 0;
    this.countNo = 0;
    this.countDeleted = 0;
    this.deletedCards = [];

    // end review div
    this.elFinish = $$<HTMLElement>(".JSEndButton")[0]!;
  }

  /**
   * proxy which typecasts the card data, and *always* returns a valid card
   *
   */
  getFlashcardData(): TCardData {
    return this.oReview.getFlashcardData() as TCardData;
  }

  /**
   * Update the visible stats to the latest server hit,
   * and setup form data for redirection to the Review Summary page.
   *
   */
  onEndReview() {
    console.log("KanjiReview.onEndReview()");

    this.updateStatsPanel();

    const elFrm = $$("#uiFcRedirectForm")[0] as HTMLFormElement;

    // set form data and redirect to summary with POST
    elFrm.method = "post";
    elFrm.action = this.options.end_url;
    (elFrm.elements.namedItem("fc_deld") as HTMLInputElement).value =
      this.deletedCards.join(",");
    elFrm.submit();
  }

  onFlashcardCreate() {
    // console.log("KanjiReview.onFlashcardCreate()");

    // Show panels when first card is loaded
    if (this.oReview.getPosition() === 0) {
      this.elStats.style.display = "block";
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

  onFlashcardUndo(answer: TCardAnswer) {
    this.updateAnswerStats(answer.id, answer.r, true);
  }

  onFlashcardState(iState: number) {
    // console.log("onFlashcardState(%d)", iState);
    $$("#uiFcButtons0").display(iState === 0);
    $$("#uiFcButtons1").display(iState !== 0);
  }

  /**
   *
   * @param sActionId cf. eventdispatcher
   * @param oEvent ...
   */
  onAction(sActionId: string, oEvent: Event) {
    let cardRating: TReviewRating | undefined;

    console.log("KanjiReview.onAction(%o)", sActionId);

    // help dialog
    if (sActionId === "help") {
      const dialog = new KoDialog({
        template: "#JsFcHelpDlg",
        align: [$$<HTMLElement>(".JSBtnHelp")[0]!, "bl", "tl"],
        dismiss: true,
        mask: true,
      });
      dialog.show();

      return false;
    }

    const cardData = this.oReview.getFlashcardData();

    // flashcard is loading
    if (!cardData) return false;

    const isAgain = cardData.isAgain;

    if (sActionId === "story") {
      this.toggleEditStory();
      return false;
    }

    if (sActionId === "dict") {
      this.toggleDictDialog();
      return false;
    }

    switch (sActionId) {
      case "JSFcMenu":
        this.flashcardMenu();
        break;    

      case "flip":
        if (
          oEvent.type === "click" &&
          hasClass(oEvent.target as HTMLElement, "JsLink")
        ) {
          // pass through so the link functions
          return true;
        }
        if (this.oReview.getFlashcardState() === 0) {
          this.oReview.setFlashcardState(1);
        }
        break;

      case "undo":
        if (this.oReview.getNumUndos() > 0) {
          this.oReview.undo();
        }
        break;

      case "end":
        this.elFinish.style.display = "none";
        this.oReview.endReview(); // this will notify onEndReview()
        break;

      case "skip":
        this.rateCard("skip");
        break;

      case "no":
        cardRating = "no";
        break;

      case "again":
        cardRating = "again";
        break;

      case "hard":
        cardRating = isAgain ? "again-hard" : "hard";
        break;

      case "yes":
        cardRating = isAgain ? "again-yes" : "yes";
        break;

      case "easy":
        cardRating = isAgain ? "again-easy" : "easy";
        break;
    }

    if (cardRating) {
      // "No" answer doesn't require flipping the card first (issue #163)
      if (this.oReview.getFlashcardState() === 1 || sActionId === "no") {
        this.rateCard(cardRating);
      }
    }

    return false;
  }

  toggleEditStory() {
    if (this.editStoryDialog && this.editStoryDialog.isOpen()) {
      this.editStoryDialog.hide();
    } else {
      const { id: ucsId } = this.getFlashcardData();
      if (!this.editStoryDialog || ucsId !== this.editStoryDialogId) {
        this.editStoryDialog?.destroy();
        this.editStoryDialog = new EditStoryDialog(ucsId);
      }
      this.editStoryDialogId = ucsId;
      this.editStoryDialog.show();
    }
  }

  toggleDictDialog() {
    if (this.dictDialog && this.dictDialog.isVisible()) {
      this.dictDialog.hide();
    } else {
      const { id: ucsId } = this.getFlashcardData();

      if (!this.dictDialog) {
        this.dictDialog = new DictLookupDialog();
      }

      this.dictDialog.show();
      this.dictDialog.load(ucsId);
    }
  }

  /**
   * Rate card (or mark action like delete/skip), then forward.
   *
   */
  rateCard(rating: TReviewRating) {
    const cardData = this.getFlashcardData();

    const answer: TCardAnswer = { id: cardData.id, r: rating };

    this.oReview.answerCard(answer);
    this.updateAnswerStats(answer.id, rating, false);

    this.oReview.forward();
  }

  /**
   * Event handler for Flashcard Menu > Skip.
   */
  onFlashcardSkip() {
    this.rateCard("skip");
    this.oEditFlashcard?.destroy(); // close the dialog
    this.oEditFlashcard = null;
  }

  onFlashcardDelete() {
    this.rateCard("delete");
    this.oEditFlashcard?.destroy(); // close the dialog
    this.oEditFlashcard = null;
  }

  /**
   * The little wrench icon that opens the menu contains:
   *
   *  data-param  {"review":1}    JSON data passed on the the menu ajax post (plus ucs id)
   *  data-uri                    Flashcard Edit Dialog ajax url
   *
   */
  flashcardMenu() {
    const el = $$("#uiFcMenu")[0] as HTMLElement;
    const { id: ucsId } = this.oReview.getFlashcardData()!;

    el.classList.add("active");

    // reload the edit flashcard menu when changed flashcard
    if (ucsId !== this.oEditFlashcardId) {
      this.oEditFlashcardId = ucsId;

      if (this.oEditFlashcard) {
        this.oEditFlashcard.destroy();
        this.oEditFlashcard = null;
      }
    }

    if (!this.oEditFlashcard) {
      this.oEditFlashcard = new EditFlashcardDialog(
        ucsId,
        [el, "br", "tr"],
        true
      );
    } else {
      this.oEditFlashcard.show();
    }

    return false;
  }

  updateStatsPanel() {
    //  console.log('KanjiReview.updateStatsPanel()');
    const total = this.oReview.numCards;

    // review progress (don't show "4 of 3" after answering last card)
    const pos = Math.min(this.oReview.numRated + 1, this.oReview.numCards);

    const elCount = $$(".JSCardsCount")[0];
    const text =
      `Card <em>${pos}&nbsp;of&nbsp;${this.oReview.numCards}</em>` +
      (this.oReview.numAgain
        ? `&nbsp;&nbsp;(Again <em>${this.oReview.numAgain}</em>)`
        : "");
    if (elCount) elCount.innerHTML = text;

    // update progress bar
    let pct = pos > 0 ? Math.ceil((pos * 100) / total) : 0;
    pct = Math.min(pct, 100);
    this.elProgressBar.style.width = (pct > 0 ? pct : 0) + "%";
  }

  /**
   *
   * @param id ... the card's id (the kanji UCS code)
   */
  updateAnswerStats(id: TUcsId, rating: TReviewRating, isUndo: boolean) {
    // cf. FlashcardReview.php const
    let yes = (
      [FCRATE.YES, FCRATE.AGAIN_YES, FCRATE.EASY, FCRATE.AGAIN_EASY] as string[]
    ).includes(rating)
      ? 1
      : 0;
    let deld = rating === FCRATE.DELETE ? 1 : 0;
    let no = ([FCRATE.NO, FCRATE.HARD, FCRATE.AGAIN_HARD] as string[]).includes(
      rating
    )
      ? 1
      : 0;

    if (isUndo) {
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

  updateDeletedCards(ucsId: TUcsId, count: number) {
    this.countDeleted += count;

    if (count > 0) {
      this.deletedCards.push(ucsId);
    } else if (count < 0) {
      this.deletedCards.pop();
    }

    $$(".JSFcDeleted").display(this.countDeleted > 0);
    const elCount = $$(".JSFcDeleted em")[0];
    if (elCount) elCount.innerHTML = "" + this.countDeleted;
    const elDeleted = $$(".JSFcDeletedK span")[0];
    if (elDeleted) elDeleted.innerHTML = this.getDeletedCards();
  }

  getDeletedCards() {
    return "&#" + this.deletedCards.join(";&#") + ";";
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
