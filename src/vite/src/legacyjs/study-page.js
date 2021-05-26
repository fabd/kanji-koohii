import $$, { domGet } from "@lib/koohii/dom";
import actb from "@old/autocomplete.js";
import EventDelegator from "@old/ui/eventdelegator";

import EditFlashcardDialog from "@old/revtk/EditFlashcardDialog";
import SharedStoriesComponent from "@old/revtk/SharedStoriesComponent";

const CLASS_ACTIVE = "active";

const StudyPage = {
  /** @type {{ URL_SEARCH: string }?} */
  options: null,

  /**
   *
   * @param {{ URL_SEARCH: string }} options
   */
  initialize: function (options) {
    var el;

    // otpions & php constants
    this.options = options;

    // references
    this.elSearch = domGet("txtSearch");

    // quick search autocomplete
    var actb1 = (this.actb1 = new actb(this.elSearch, kwlist));
    actb1.onChangeCallback = this.quicksearchOnChangeCallback.bind(this);
    actb1.onPressEnterCallback = this.quicksearchEnterCallback.bind(this);

    // function move to _SideColumnView.php for CJK lang attributes
    actb1.actb_extracolumns = this.actb_extracols; // _SideColumnView.php
    /*
      function(iRow) {
        return '<span class="f">'+(iRow+1)+'</span><span class="k cj-t">&#'+kklist.charCodeAt(iRow)+';</span>';
      };*/

    // clicking in quick search box selects the text
    $$(this.elSearch).on("focus", function (ev) {
      if ((el = ev.target) && el.value !== "") {
        el.select();
      }
    });

    // auto focus search box
    if (this.elSearch && this.elSearch.value === "") {
      this.elSearch.focus();
    }

    if ((el = $$("#DictStudy")[0])) {
      this.initDictionary(el);
    }

    console.log("studyyy");
    if ((el = domGet("SharedStoriesComponent"))) {
      this.sharedStoriesComponent = new SharedStoriesComponent(this, el);
    }

    if ((el = $$("#EditFlashcard")[0])) {
      this.elEditFlashcard = el;
      var ed = new EventDelegator(el, "click");
      ed.on("JsEditFlashcard", this.onEditFlashcard, this);
    }
  },

  initDictionary: function (el) {
    $$("#DictHead").on("click", this.toggleDictionary.bind(this));
    this.dictVisible = false;
    this.dictPanel = null;
  },

  toggleDictionary: function (e, el) {
    var visible = !this.dictVisible,
      $elBody = $$("#JsDictBody")[0],
      ucsId = elBody.dataset.ucs;

    $elBody.toggle(visible);
    this.dictVisible = visible;

    if (!this.dictPanel) {
      // use inner div set in the php template
      console.log("yya");
      var elMount = $elBody.down(".JsMount");
      var inst = VueInstance(Koohii.UX.KoohiiDictList, elMount, {}, true);
      inst.load(ucsId);

      this.dictPanel = true;
    }
  },

  onSearchBtn: function (e) {
    var text = this.elSearch.value;
    this.quicksearchOnChangeCallback(text);
    e.preventDefault();
    return false;
  },

  onEditFlashcard: function (ev, el) {
    var data = el.dataset;

    function onMenuResponse(result) {
      // update icon to reflect new flashcard state
      var z = { added: "1", deleted: "0" };
      if (z.hasOwnProperty(result)) {
        var div = el.parentNode;
        div.className = div.className.replace(
          /\bis-toggle-\d\b/,
          "is-toggle-" + z[result]
        );
      }
    }

    function onMenuHide() {
      // clear icon focus state when dialog closes
      el.classList.remove(CLASS_ACTIVE);
    }

    el.classList.add(CLASS_ACTIVE);

    if (!this.oEditFlashcard) {
      this.oEditFlashcard = new EditFlashcardDialog(
        data.uri,
        JSON.parse(data.param),
        [this.elEditFlashcard, "tr", "br"],
        {
          events: {
            onMenuResponse: onMenuResponse,
            onMenuHide: onMenuHide,
            scope: this,
          },
        }
      );
    } else {
      this.oEditFlashcard.show();
    }

    return false;
  },

  /**
   * Auto-complete onchange callback, fires after user selects
   * something from the drop down list.
   *
   * @param  string  text  String typed into the searchbox
   *
   * @see    autocomplete.js
   */
  quicksearchOnChangeCallback: function (text) {
    if (text.length > 0) {
      // Lookup the first kanji if there is any kanji in the search string, ignore other characters
      // Regexp is equivalent of \p{InCJK_Unified_Ideographs}
      if (/([\u4e00-\u9fff])/.test(text)) {
        text = RegExp.$1;
      }

      window.location.href =
        this.options.URL_SEARCH + "/" + this.anesthetizeThisBloodyUri(text);
      return true;
    }
  },

  /**
   * Auto-complete ENTER key callback.
   *
   * @see    autocomplete.js
   */
  quicksearchEnterCallback: function (text) {
    this.quicksearchOnChangeCallback(text);
  },

  /**
   * Replaces problematic characters in the url which cause trouble
   * either with parsing the route (slash) or some kind of filter on the
   * web host's side which returns a 404 for urls with uncommon dot patterns
   * (eg. "/study/kanji/made in...").
   *
   * On the backend side, the dashes become wildcards.
   */
  anesthetizeThisBloodyUri: function (annoyingUri) {
    var s = annoyingUri.replace(/[\/\.]/g, "-");
    return encodeURIComponent(s);
  },
};

export default StudyPage;
