/**
 * Dialog to edit a story, within Flashcard Review or other pages.
 *
 */

import VueInstance from "@lib/helpers/vue-instance";
import $$ from "@/lib/dom";
import { type TronInst } from "@/lib/tron";
import KoDialog from "@/components/KoDialog";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";
import { getApi } from "@/app/api/api";
import { type EditStoryResponse } from "@/app/api/models";
import KoohiiLoading from "@/vue/KoohiiLoading";

export default class EditStoryDialog {
  // unique id to find when we need to reload the dialog
  ucsId: number = 0;
  dialog: KoDialog;
  editStory: TVueInstanceRef<typeof KoohiiEditStory> | null = null;

  /**
   * @param url - The URL to load the dialog content from.
   * @param ucsId - The UCS code for the kanji to display.
   */
  constructor() {
    const isMobile = window.innerWidth <= 700;

    this.dialog = new KoDialog({
      align: [$$<HTMLElement>("#uiFcMain")[0]!, "tl", "tl", [-10, -36]],
      mask: true,
      mobile: isMobile,
      close: true,
      title: "Edit Story",
      width: "500px",
    });
  }

  /**
   * Loads a new kanji into the dialog. Does nothing if the same kanji is already loaded.
   *
   * @param ucsId - The UCS code of the kanji to load.
   */
  load(ucsId: number): void {
    console.log("EditStoryDialog::load(%d)", ucsId);

    // Don't load the same kanji twice in a row
    if (this.ucsId === ucsId) {
      return;
    }

    const elBody = this.dialog.getBody();

    // clear the contents so it doesn't show behind the loading mask
    elBody.innerHTML = "";

    KoohiiLoading.show({ target: elBody });

    getApi()
      .legacy.getEditStory(ucsId, true)
      .then((tron: TronInst<EditStoryResponse>) => {
        const props = tron.getProps();
        // unmount last Vue instance
        this.destroy();

        const vueProps = {
          kanjiData: props.kanjiData,
          custKeyword: props.custKeyword,
          isReviewMode: true,
          initFavoriteStory: props.initFavoriteStory,
          initStoryEdit: props.initStoryEdit,
          initStoryView: props.initStoryView,
          initStoryPublic: props.initStoryPublic,
        };

        const elMount = this.dialog.getBody();
        this.editStory = VueInstance(KoohiiEditStory, elMount, vueProps);
      })
      .finally(() => {
        KoohiiLoading.hide();
      });

    this.dialog.getFooter().innerHTML = `
      <button class="ko-Btn ko-Btn--large ko-Btn--lime w-full JSDialogHide">Close</button>
    `;

    this.ucsId = ucsId;
  }

  show(): void {
    console.log("EditStoryDialog::show()");
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
  }

  isOpen(): boolean {
    return this.dialog.isVisible();
  }
}
