import { type TronInst } from "@lib/tron";
import KoDialog, { type KoDialogAnchor, type KoDialogOptions } from "@/components/KoDialog";
import AjaxPanel from "@old/ajaxpanel";
import KoEditFlashcard from "@/vue/KoEditFlashcard.vue";
import VueInstance from "@lib/helpers/vue-instance";
import { type KanjiData, type ReviewData } from "@/app/api/models";
import { urlFor } from "@/lib/koohii";

type EditFlashcardResponse = {
  kanjiData: KanjiData;
  cardData: ReviewData;
};

export default class EditFlashcardDialog {
  private dialog: KoDialog | null = null;
  private vueInst: TVueInstanceRef<typeof KoEditFlashcard> | null = null;
  private isReviewMode: boolean;

  constructor(
    ucsId: TUcsId,
    align: KoDialogAnchor,
    isReviewMode: boolean
  ) {
    this.isReviewMode = isReviewMode;

    const dlgopts: KoDialogOptions = {
      align: align,
      close: true,
      dismiss: true,
      mask: true,
      title: "Edit Flashcard",
      width: "280px",
    };

    this.dialog = new KoDialog(dlgopts);
    
    const elBody = this.dialog.getBody();

    const ajaxPanel = new AjaxPanel(elBody, {
      events: {
        onResponse: this.onPanelResponse.bind(this),
      },
    });

    ajaxPanel.get({ ucs: ucsId }, urlFor("/flashcards/edit"));
    
    this.dialog.show();
  }

  show() {
    this.dialog!.show();
  }

  /*
  onHide() {
    console.log("EditFlashcardDialog::onHide()");

    // clumsy page reload uri received from last response TRON "reload" property
    if (this.reload) {
      window.location.href = this.reload;
      return false;
    }

    this.eventDispatcher.notify("onMenuHide");
  }*/

  onPanelResponse(tron: TronInst<EditFlashcardResponse>) {
    const props = tron.getProps();
    const elMount = this.dialog!.getBody();

    this.vueInst = VueInstance(KoEditFlashcard, elMount, {
      kanjiData: props.kanjiData,
      cardData: props.cardData,
      reviewMode: this.isReviewMode
    });
  }

  destroy() {
    this.vueInst?.unmount();
    this.dialog!.destroy();
    this.dialog = null;
  }
}

