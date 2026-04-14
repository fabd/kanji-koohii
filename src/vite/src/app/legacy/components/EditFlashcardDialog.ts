import { type TronInst } from "@lib/tron";
import { KoAjaxDialog } from "@/vue/KoAjaxDialog";
import { type KoDialogAnchor, type KoDialogOptions } from "@/vue/KoDialog";
import KoEditFlashcard from "@/vue/KoEditFlashcard.vue";
import VueInstance from "@lib/helpers/vue-instance";
import { type GetEditFlashcardResponse } from "@/app/api/models";

export default class EditFlashcardDialog {
  private dialog: KoAjaxDialog | null = null;
  private vue: TVueInstanceRef<typeof KoEditFlashcard> | null = null;

  constructor(ucsId: TUcsId, align: KoDialogAnchor, isReviewMode: boolean) {
    const dlgopts: KoDialogOptions = {
      align: align,
      close: true,
      dismiss: true,
      mask: true,
      title: "Edit Flashcard",
      width: "280px",
    };

    this.dialog = new KoAjaxDialog(
      "/flashcards/edit",
      { ucs: ucsId },
      dlgopts,
      (tron: TronInst<GetEditFlashcardResponse>) => {
        const { kanjiData, cardData } = tron.getProps();
        const mount = this.dialog!.getBody();

        this.vue = VueInstance(KoEditFlashcard, mount, {
          kanjiData,
          cardData,
          reviewMode: isReviewMode,
        });
      }
    );

    this.dialog.show();
  }

  show() {
    this.dialog!.show();
  }

  destroy() {
    this.vue?.unmount();
    this.dialog!.destroy();
    this.dialog = null;
  }
}
