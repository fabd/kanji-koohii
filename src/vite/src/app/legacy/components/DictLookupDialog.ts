import VueInstance from "@/lib/helpers/vue-instance";
import KoohiiDictList from "@/vue/KoohiiDictList.vue";
import KoDialog from "@/vue/KoDialog";

export default class DictLookupDialog {
  // unique id to find when we need to reload the dialog
  private ucsId = 0;
  private dialog: KoDialog;
  private isMobile: boolean;
  private vueInst: TVueInstanceRef<typeof KoohiiDictList> | null = null;

  constructor() {
    this.isMobile = window.innerWidth <= 720;

    this.dialog = new KoDialog({
      align: [document.body, "tl", "tl", [10, 10]],
      mask: true,
      dismiss: true,
      mobile: this.isMobile,
      close: true,
      title: "Dictionary",
      width: "400px",
    });
  }

  load(ucsId: number) {
    // Don't load the same kanji twice in a row
    if (this.ucsId === ucsId) {
      return;
    }

    if (this.vueInst) {
      this.vueInst.unmount();
      this.vueInst = null;
    }

    // note: mounting the Vue 3 component will replace our "loading" div
    const elMount = this.dialog.getBody();
    this.vueInst = VueInstance(KoohiiDictList, elMount);

    this.vueInst.vm.load(ucsId);

    if (this.isMobile) {
      this.dialog.getFooter().innerHTML = `
      <button class="ko-Btn ko-Btn--large ko-Btn--lime w-full JSDialogHide">Close</button>
    `;
    }

    // note: this also prevents spamming load() while ajax is in progress
    this.ucsId = ucsId;
  }

  show() {
    this.dialog.show();
  }

  hide() {
    this.dialog.hide();
  }

  isVisible() {
    return this.dialog.isVisible();
  }
}
