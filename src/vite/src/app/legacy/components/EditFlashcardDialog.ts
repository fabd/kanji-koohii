import { type TronInst } from "@lib/tron";
import KoDialog, {
  type KoDialogAnchor,
  type KoDialogOptions,
} from "@/components/KoDialog";
import KoEditFlashcard from "@/vue/KoEditFlashcard.vue";
import VueInstance from "@lib/helpers/vue-instance";
import { getApi } from "@/app/api/api";
import { type GetEditFlashcardResponse } from "@/app/api/models";
import KoohiiLoading from "@/vue/KoohiiLoading";

export default class EditFlashcardDialog {
  private dialog: KoDialog | null = null;
  private vueInst: TVueInstanceRef<typeof KoEditFlashcard> | null = null;

  constructor(ucsId: TUcsId, align: KoDialogAnchor, isReviewMode: boolean) {
    this.dialog = new KoDialog({
      align: align,
      close: true,
      dismiss: true,
      mask: true,
      title: "Edit Flashcard",
      width: "280px",
    });

    const mount = this.dialog!.getBody();

    KoohiiLoading.show({ target: mount });

    getApi()
      .getEditFlashcard(ucsId)
      .then((tron: TronInst<GetEditFlashcardResponse>) => {
        const { kanjiData, cardData } = tron.getProps();

        this.vueInst = VueInstance(KoEditFlashcard, mount, {
          kanjiData,
          cardData,
          reviewMode: isReviewMode,
        });
      })
      .finally(() => {
        KoohiiLoading.hide();
      });

    this.dialog.show();
  }

  show() {
    this.dialog!.show();
  }

  destroy() {
    this.vueInst?.unmount();
    this.dialog!.destroy();
    this.dialog = null;
  }
}
