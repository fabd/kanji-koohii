// FIXME: legacy Edit Keyword dialog, should be a Vue

import $$ from "@lib/dom";
import { type TronInst } from "@lib/tron";
import AjaxDialog from "@old/ajaxdialog";
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
  private options: any;

  private ucsId: TUcsId;

  private callback: EditKeywordCallback;

  private dialog: AjaxDialog | null = null;

  private vueInst: TVueInstanceRef | null = null;

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
    options: Dictionary,
    callback: EditKeywordCallback
  ) {
    console.log("EditKeywordDialog(%d %o)", ucsId, options);

    this.ucsId = ucsId;
    this.options = options;
    this.callback = callback;

    const dlgopts: AjaxDialogOpts = {
      requestUri: `/study/editkeyword/id/${ucsId}`,
      requestData: options.params,
      skin: isMobile ? "rtk-mobl-dlg" : "rtk-skin-dlg",
      mobile: isMobile,
      close: !isMobile,
      width: 380, // make sure this matches width set in CSS
      scope: this,
      events: {
        onDialogResponse: this.onDialogResponse,
        onDialogHide: this.onHide,
      },
    };

    // position dialog
    if (!isMobile) {
      dlgopts.context = options.context;
    }

    this.dialog = new AjaxDialog(null, dlgopts);
    this.dialog.show();
  }

  // Show again, after it is closed with the YUI close button.
  show() {
    this.dialog!.show();
    this.vueInst?.vm.focusInput();
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

    this.vueInst = VueInstance(KoEditKeyword, elMount, {
      ucsId: props.ucs_id,
      origKeyword: props.orig_keyword,
      userKeyword: props.user_keyword,
      maxLength: props.max_length,
      isManagePage: this.options.isManagePage || false,
      onSuccess: (keyword: string, tabKey: boolean) => {
        this.dialog?.hide();
        this.callback(keyword, tabKey);
      }
    });
  }

  // Copy keyword back into the main page
  // If JsTron property "next" is returned, the callback for the Manage page edits the next keyword

  onHide() {
    // keep the dialog in the page
    return false;
  }
}
