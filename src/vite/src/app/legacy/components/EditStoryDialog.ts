/**
 * Dialog to edit a story, within Flashcard Review or other pages.
 *
 */

import VueInstance from "@lib/helpers/vue-instance";
import $$ from "@/lib/dom";
import AjaxPanel from "@old/ajaxpanel";
import { type TronInst } from "@/lib/tron";
import KoDialog, { type KoDialogOptions } from "@/components/KoDialog";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";
import { type KanjiData } from "@/app/api/models";
import { API_URL_STUDY_EDITSTORY } from "@/app/api/api";

type EditStoryResponse = {
  kanjiData: KanjiData;
  custKeyword: string;

  isReviewMode: boolean;
  initFavoriteStory: boolean; /* the user's story is empty, display favorite story */

  initStoryEdit: string; /* the current saved story (edit mode) */
  initStoryPublic: boolean; /* whether the current story is public */
  initStoryView: string; /* the formatted story (view mode) */
};

export default class EditStoryDialog {
  // unique id to find when we need to reload the dialog
  ucsId: number = 0;
  dialog: KoDialog;
  editStory: TVueInstanceRef<typeof KoohiiEditStory> | null = null;
  ajaxPanel: AjaxPanel | null = null;

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

    if (!this.ajaxPanel) {
      this.ajaxPanel = new AjaxPanel(this.dialog.getBody(), {
        events: {
          onResponse: this.onPanelResponse.bind(this),
        },
      });
    }

    // clear the contents so it doesn't show behind the loading mask
    this.dialog.getBody().innerHTML = "";

    this.ajaxPanel.get(
      { ucsCode: ucsId, reviewMode: true },
      API_URL_STUDY_EDITSTORY
    );

    this.dialog.getFooter().innerHTML = `
      <button class="ko-Btn ko-Btn--large ko-Btn--lime w-full JSDialogHide">Close</button>
    `;

    this.ucsId = ucsId;
  }

  /** Shows the dialog. */
  show(): void {
    console.log("EditStoryDialog::show()");
    this.dialog.show();
  }

  /** Hides the dialog. */
  hide(): void {
    this.dialog.hide();
  }

  /** Called when the dialog is hidden; keeps the dialog in the page. */
  onDialogHide(): false {
    console.log("EditStoryDialog::hide()");

    // fabd: removed "cancel edit mode"... what if user edited, then closed by mistake,
    //   not really necessary to undo edit mode when the Edit Story dialog is hidden.

    // keep the dialog in the page
    return false;
  }

  onPanelResponse(tron: TronInst<EditStoryResponse>) {
    // console.log('ondialogresponse tron %o', tron);

    // unmount last Vue instance
    this.destroy();

    const props = tron.getProps();

    const propsData = {
      kanjiData: props.kanjiData,
      custKeyword: props.custKeyword,

      isReviewMode: true,
      initFavoriteStory: props.initFavoriteStory,

      initStoryEdit: props.initStoryEdit,
      initStoryView: props.initStoryView,
      initStoryPublic: props.initStoryPublic,
    };

    const elMount = this.dialog.getBody();

    this.editStory = VueInstance(KoohiiEditStory, elMount, propsData);
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
