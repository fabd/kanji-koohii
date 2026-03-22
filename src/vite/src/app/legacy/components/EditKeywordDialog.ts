import { type TronInst } from "@lib/tron";
import AjaxPanel from "@old/ajaxpanel";
import KoDialog, { type KoDialogAnchor, type KoDialogOptions } from "@/components/KoDialog";
import VueInstance from "@lib/helpers/vue-instance";

import KoEditKeyword from "@/vue/KoEditKeyword.vue";

type EditKeywordResponse = {
  ucs_id: TUcsId;
  orig_keyword: string;
  user_keyword: string;
  max_length: number;
};

export type EditKeywordCallback = (keyword: string, next?: boolean) => void;

const isMobile = window.innerWidth <= 700;

export default class EditKeywordDialog {
  private isManagePage: boolean;
  private ucsId: TUcsId;
  private callback: EditKeywordCallback;
  private dialog: KoDialog | null = null;
  private vueInst: TVueInstanceOf<typeof KoEditKeyword> | null = null;

  /**
   *
   * Options:
   *   context    Sets the context element to align the dialog (see YUI2 Overlay).
   *
   * @param ucsId
   * @param options   params (AjaxDialog requestData), context (YUI2 Panel option)
   * @param callback   Callback to insert the updated keyword back into the page
   */
  constructor(
    ucsId: TUcsId,
    align: KoDialogAnchor,
    callback: EditKeywordCallback,
    isManagePage: boolean = false
  ) {
    console.log("EditKeywordDialog(%d)", ucsId);

    this.ucsId = ucsId;
    this.callback = callback;
    this.isManagePage = isManagePage;

    const dlgopts: KoDialogOptions = {
      align: align,
      mask: true,
      mobile: isMobile,
      close: true,
      title: `Customize Keyword for ${String.fromCodePoint(ucsId)}`,
      width: "380px",
    };

    this.dialog = new KoDialog(dlgopts);

    const elBody = this.dialog.getBody();

    const ajaxPanel = new AjaxPanel(elBody, {
      events: {
        onResponse: this.onDialogResponse.bind(this),
      },
    });

    ajaxPanel.get(null, `/study/editkeyword/id/${ucsId}`);
    
    this.dialog.show();
  }

  // Show again, after it is closed with the YUI close button.
  show() {
    this.dialog!.show();
    this.vueInst?.focusInput();
  }

  destroy() {
    this.dialog!.destroy();
    this.dialog = null;
  }

  onDialogResponse(tron: TronInst<EditKeywordResponse>) {
    console.log("EditKeywordDialog::onDialogResponse()");
    const elMount = this.dialog!.getBody();
    const props = tron.getProps();

    // if (this.vueInst) this.vueInst.unmount();

    const { vm } = VueInstance(KoEditKeyword, elMount, {
      ucsId: props.ucs_id,
      origKeyword: props.orig_keyword,
      userKeyword: props.user_keyword,
      maxLength: props.max_length,
      isManagePage: this.isManagePage,
      onSuccess: (keyword: string, tabKey: boolean) => {
        this.dialog?.hide();
        this.callback(keyword, tabKey);
      },
    });

    this.vueInst = vm;
  }

  // Copy keyword back into the main page
  // If JsTron property "next" is returned, the callback for the Manage page edits the next keyword

  onHide() {
    // keep the dialog in the page
    return false;
  }
}
