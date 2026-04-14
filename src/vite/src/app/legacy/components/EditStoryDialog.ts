/**
 * Dialog to edit a story, within Flashcard Review or other pages.
 *
 */
import VueInstance from "@lib/helpers/vue-instance";
import $$ from "@/lib/dom";
import { type TronInst } from "@/lib/tron";
import { KoAjaxDialog } from "@/vue/KoAjaxDialog";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";
import { type EditStoryResponse } from "@/app/api/models";

export default class EditStoryDialog {
  dialog: KoAjaxDialog;
  editStory: TVueInstanceRef<typeof KoohiiEditStory> | null = null;

  /**
   *
   * @param ucsId - The UCS code for the kanji
   */
  constructor(ucsId: TUcsId) {
    const isMobile = window.innerWidth <= 700;

    this.dialog = new KoAjaxDialog(
      "/study/editstory",
      {
        ucsCode: ucsId,
        reviewMode: true,
      },
      {
        align: [$$<HTMLElement>("#uiFcMain")[0]!, "tl", "tl", [-10, -36]],
        mask: true,
        mobile: isMobile,
        close: true,
        title: "Edit Story",
        width: "500px",
      },
      (tron: TronInst<EditStoryResponse>) => {
        const props = tron.getProps();

        const vueProps = {
          kanjiData: props.kanjiData,
          custKeyword: props.custKeyword,
          isReviewMode: true,
          initFavoriteStory: props.initFavoriteStory,
          initStoryEdit: props.initStoryEdit,
          initStoryView: props.initStoryView,
          initStoryPublic: props.initStoryPublic,
        };

        const mount = this.dialog.getBody();
        this.editStory = VueInstance(KoohiiEditStory, mount, vueProps);

        this.dialog.getFooter().innerHTML = `
<button class="ko-Btn ko-Btn--large ko-Btn--lime w-full JSDialogHide">Close</button>
    `;
      }
    );
  }

  show(): void {
    this.dialog.show();
  }

  hide(): void {
    this.dialog.hide();
  }

  destroy() {
    if (this.editStory) {
      this.editStory.unmount();
      this.editStory = null;
    }
    this.dialog.destroy();
  }

  isOpen(): boolean {
    return this.dialog.isVisible();
  }
}
