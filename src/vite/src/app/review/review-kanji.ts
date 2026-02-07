/**
 * RtK Kanji flashcard review.
 *
 * Options:
 *
 *   fcr_options   Options to setup FlashcardReview
 *   end_url       Url to redirect to at the end of the review
 *
 */

import $$, { DomJS, asHtmlElement, hasClass } from "@lib/dom";
import AjaxDialog from "@old/ajaxdialog";
import DictLookupDialog from "@old/components/DictLookupDialog";
import EditFlashcardDialog from "@old/components/EditFlashcardDialog";
import EditStoryDialog from "@old/components/EditStoryDialog";
import FlashcardReview, { FCRATE } from "@app/review/FlashcardReview";
import ReviewPage from "@app/review/ReviewPage";

export default class KanjiReview {
  options: TKanjiReviewProps;

  oReview: FlashcardReview;

  $elStats: DomJS<Element>;
  elProgressBar: HTMLElement;
  elsCount: DomJS<Element>;

  dictDialog: DictLookupDialog | null = null;
  oEditFlashcard: EditFlashcardDialog | null = null;
  oEditFlashcardId: TUcsId = 0;
  editStoryDialog: EditStoryDialog | null = null;

  deletedCards: number[] = [];

  elAnswerPass: Element;
  elAnswerFail: Element;
  countYes: number;
  countNo: number;
  countDeleted: number;

  elFinish: HTMLElement;
  reviewPage: ReviewPage;

  /**
   *
   * @param fcrOptions ... options for FlashcardReview instance
   * @param props ... props for Vue component (TBD refactor)
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
    if (!this.getOption("freemode")) {
      this.reviewPage.addShortcutKey("h", "hard");
      this.reviewPage.addShortcutKey("e", "easy");
    }

    // added number keys to answer with just left hand
    this.reviewPage.addShortcutKey("1", "no");
    this.reviewPage.addShortcutKey("3", "yes");
    if (!this.getOption("freemode")) {
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

    // Disabled because it's next to (F)lip Card
    //this.reviewPage.addShortcutKey('d', 'delete');

    // flashcad container
    // this.elFlashcard = $$('.uiFcCard')[0];

    // stats panel
    this.$elStats = $$("#uiFcStats");
    this.elsCount = $$("#uiFcProgressBar .count");
    this.elProgressBar = asHtmlElement($$("#review-progress span")[0]);

    // answer stats
    this.elAnswerPass = $$("#uiFcStats .JsPass")[0]!;
    this.elAnswerFail = $$("#uiFcStats .JsFail")[0]!;
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
   */
  getOption(name: keyof TKanjiReviewProps) {
    return this.options[name];
  }

  /**
   *
   */
  getOptionAsStr(name: keyof TKanjiReviewProps): string {
    return this.options[name] as string;
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
    elFrm.action = this.getOptionAsStr("end_url");
    (elFrm.elements.namedItem("fc_deld") as HTMLInputElement).value = this.deletedCards.join(",");
    elFrm.submit();
  }

  onFlashcardCreate() {
    // console.log("KanjiReview.onFlashcardCreate()");

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
      const dlg = new AjaxDialog("#JsFcHelpDlg", {
        useMarkup: true,
        context: ["JsBtnHelp", "tl", "bl", null, [0, 0]],
        skin: "rtk-skin-dlg",
        mobile: true,
        close: false,
      });
      dlg.show();

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
      case "fcmenu":
        this.flashcardMenu();
        break;
      case "delete":
        this.rateCard("delete");
        break;

      case "flip":
        if (oEvent.type === "click" && hasClass(asHtmlElement(oEvent.target), "JsLink")) {
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
      if (this.oReview.getFlashcardState() > 0 || sActionId === "no") {
        this.rateCard(cardRating);
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
        this.editStoryDialog = new EditStoryDialog(this.getOptionAsStr("editstory_url"), oCardData.id);
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
   * The little wrench icon that opens the menu contains:
   *
   *  data-param  {"review":1}    JSON data passed on the the menu ajax post (plus ucs id)
   *  data-uri                    Flashcard Edit Dialog ajax url
   *
   */
  flashcardMenu() {
    const el = $$("#uiFcMenu")[0] as HTMLElement;

    const data = el.dataset as { uri: string; param: string };

    const oCardData = this.oReview.getFlashcardData() as TCardData;

    const onMenuHide = () => {
      // clear icon focus state when dialog closes
      el.classList.remove("active");
    };

    const onMenuItem = (menuid: string) => {
      if (menuid === "confirm-delete") {
        // set flashcard answer that tells server to delete the card
        this.rateCard("delete");
        return true;
      } else if (menuid === "skip") {
        this.rateCard("skip");
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
      const params = {
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
    const total = this.oReview.numCards;

    // review progress (don't show "4 of 3" after answering last card)
    const pos = Math.min(this.oReview.numRated + 1, this.oReview.numCards);

    const elCount = $$("#uiFcProgressBar h3")[0];
    const text =
      `Card <em>${pos}&nbsp;of&nbsp;${this.oReview.numCards}</em>` +
      (this.oReview.numAgain ? `&nbsp;&nbsp;(Again <em>${this.oReview.numAgain}</em>)` : "");
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
    let yes = [FCRATE.YES, FCRATE.AGAIN_YES, FCRATE.EASY, FCRATE.AGAIN_EASY].includes(rating) ? 1 : 0;
    let no = [FCRATE.NO, FCRATE.HARD, FCRATE.AGAIN_HARD].includes(rating) ? 1 : 0;
    let deld = rating === FCRATE.DELETE ? 1 : 0;

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

    $$("#uiFcStDeld").display(this.countDeleted > 0);
    const elCount = $$("#uiFcStDeld em")[0];
    if (elCount) elCount.innerHTML = "" + this.countDeleted;
    const elDeleted = $$("#uiFcStDeldK span")[0];
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
