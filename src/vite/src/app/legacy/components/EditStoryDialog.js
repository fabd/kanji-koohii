/**
 * Dialog to edit a story, within Flashcard Review or other pages.
 *
 */
// @ts-check

import AjaxDialog from "@old/ajaxdialog";
import VueInstance from "@lib/helpers/vue-instance";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";

const isMobile = window.innerWidth <= 720;

const LOADING_WIDTH = 500;

/** @typedef {TVueInstanceOf<typeof KoohiiEditStory>} TVueKoohiiEditStory */

export default class EditStoryDialog {
  // unique id to find when we need to reload the dialog
  /** @type {number} */
  ucsId = 0;

  /** @type {AjaxDialog} */
  dialog;

  /** @type {TVueInstanceRef?} */
  editStory = null;

  /**
   * @param {string} url
   * @param {number} ucsId
   */
  constructor(url, ucsId) {
    // use unique id to find when we need to reload the dialog
    this.ucsId = ucsId;

    this.requestUri = url;

    /** @type {AjaxDialogOpts} */
    const dlgopts = {
      requestUri: this.requestUri,
      requestData: { ucsCode: ucsId, reviewMode: true },
      skin: isMobile ? "rtk-mobl-dlg" : "rtk-skin-dlg",
      mobile: isMobile,
      close: !isMobile,
      width: LOADING_WIDTH,
      scope: this,
      events: {
        onDialogResponse: this.onDialogResponse,
        onDialogDestroy: this.onDialogDestroy,
        onDialogHide: this.onDialogHide,
      },
    };

    // FIXME position dialog near flashcard to avoid current issue with centering
    // and the "ajax loading" content (ie. we don't know what the real width
    // will be until content is loaded, and we dont want dialog to move around)
    if (!isMobile) {
      dlgopts.context = ["uiFcMain", "tl", "tl", null, [-10, -36]];
    }

    this.dialog = new AjaxDialog(null, dlgopts);
    this.dialog.show();

    // loading state (first load)
    this.addCloseButton();
  }

  // Issue #106 / hacky solution but this will be refactored to Vue anyway
  // - we don't want to add a Close button at higher level in AjaxDialog
  // - we add the html for the Close button, it gets replaced by the ajax content
  addCloseButton() {
    console.log("addCloseButton()");

    var el = document.createElement("div");

    el.innerHTML =
      '<div class="uiBMenu">' +
      '<div class="uiBMenuItem">' +
      '<a class="JSDialogHide uiIBtn uiIBtnDefault" href="#"><span>Close</span></a>' +
      "</div>" +
      "</div>";

    var elBody = this.dialog.getBody();
    elBody.appendChild(el);
  }

  /** @param {number} ucsId */
  load(ucsId) {
    // Don't load the same kanji twice in a row
    if (this.ucsId === ucsId) {
      return;
    }

    // cleanup
    this.onDialogDestroy();

    // loading state (clears the previous contents of the dialog body)
    this.dialog.setBodyLoading(LOADING_WIDTH);
    this.addCloseButton();

    this.ucsId = ucsId;

    this.dialog
      .getAjaxPanel()
      .get({ ucsCode: ucsId, reviewMode: true }, this.requestUri);
  }

  show() {
    console.log("EditStoryDialog::show()");
    this.dialog.show();
  }

  hide() {
    this.dialog.hide();
  }

  onDialogHide() {
    console.log("EditStoryDialog::hide()");

    // fabd: removed "cancel edit mode"... what if user edited, then closed by mistake,
    //   not really necessary to undo edit mode when the Edit Story dialog is hidden.

    // keep the dialog in the page
    return false;
  }

  /** @param {import("@/lib/tron").TronInst} tron */
  onDialogResponse(tron) {
    // console.log('ondialogresponse tron %o', tron);

    var data = tron.getProps();

    var propsData = {
      kanjiData: data.kanjiData,
      custKeyword: data.custKeyword,

      isReviewMode: true,
      initFavoriteStory: data.initFavoriteStory,

      initStoryEdit: data.initStoryEdit,
      initStoryView: data.initStoryView,
      initStoryPublic: data.initStoryPublic,
    };

    // note! mounting the component will also remove (overwrite) our "loading" div
    const elMount = this.dialog.getBody();

    this.editStory = VueInstance(KoohiiEditStory, elMount, propsData);
  }

  onDialogDestroy() {
    // console.log('onDialogDestroy()')
    if (this.editStory) {
      this.editStory.unmount();
      this.editStory = null;
    }
  }

  isVisible() {
    return this.dialog.isVisible();
  }
}
