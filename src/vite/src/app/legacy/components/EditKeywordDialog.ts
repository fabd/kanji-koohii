import { type TronInst } from "@lib/tron";
import { KoAjaxDialog } from "@/components/KoAjaxDialog";
import { type KoDialogAnchor, type KoDialogOptions } from "@/components/KoDialog";
import VueInstance from "@lib/helpers/vue-instance";

import KoEditKeyword from "@/vue/KoEditKeyword.vue";

type EditKeywordResponse = {
  ucs_id: TUcsId;
  orig_keyword: string;
  user_keyword: string;
  max_length: number;
};

export type EditKeywordCallback = (keyword: string, next?: boolean) => void;

export default class EditKeywordDialog {
  private dialog: KoAjaxDialog | null = null;
  private vue: TVueInstanceRef<typeof KoEditKeyword> | null = null;

  /**
   *
   * Options:
   *
   * @param ucsId     UCS code
   * @param align     alignment for the dialog
   * @param callback  callback to insert the updated keyword back into the page
   */
  constructor(
    ucsId: TUcsId,
    align: KoDialogAnchor,
    callback: EditKeywordCallback,
    isManagePage: boolean = false
  ) {
    const isMobile = window.innerWidth <= 700;

    const dlgopts: KoDialogOptions = {
      align: align,
      dismiss: true,
      mask: true,
      mobile: isMobile,
      close: true,
      title: `Customize Keyword for ${String.fromCodePoint(ucsId)}`,
      width: "380px",
    };

    this.dialog = new KoAjaxDialog(
      `/study/editkeyword/id/${ucsId}`,
      null,
      dlgopts,
      (tron: TronInst<EditKeywordResponse>) => {
        const mount = this.dialog!.getBody();
        const props = tron.getProps();

        this.vue = VueInstance(KoEditKeyword, mount, {
          ucsId: props.ucs_id,
          origKeyword: props.orig_keyword,
          userKeyword: props.user_keyword,
          maxLength: props.max_length,
          isManagePage,
          onSuccess: (keyword: string, tabKey: boolean) => {
            this.dialog?.hide();
            callback(keyword, tabKey);
          },
        });
      }
    );

    this.dialog.show();
  }

  show() {
    this.dialog!.show();
    this.vue?.vm.focusInput();
  }

  destroy() {
    if (this.vue) {
      this.vue.unmount();
      this.vue = null;
    }
    this.dialog!.destroy();
    this.dialog = null;
  }
}
