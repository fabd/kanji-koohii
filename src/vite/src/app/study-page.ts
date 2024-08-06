// FIXME: this is the legacy study-page.js, should be a master Vue component someday

import $$, { domGetById } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import * as RTK from "@/lib/rtk";

import VueInstance from "@lib/helpers/vue-instance";

import actb from "@old/autocomplete.js";

import EventDelegator from "@lib/EventDelegator";
import EditFlashcardDialog from "@old/components/EditFlashcardDialog";
import KoohiiDictList from "@/vue/KoohiiDictList.vue";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";
import KoStudyLastViewed from "@/vue/KoStudyLastViewed.vue";
import SharedStoriesComponent from "@old/components/SharedStoriesComponent";

type TVueKoohiiDictList = TVueInstanceOf<typeof KoohiiDictList>;

const CLASS_ACTIVE = "active";

export default {
  elEditFlashcard: null as Element | null,

  oEditFlashcard: null as EditFlashcardDialog | null,

  elSearch: (null as any) as HTMLInputElement,

  dictVisible: false,

  dictPanel: false,

  initialize() {
    // references
    this.elSearch = domGetById<HTMLInputElement>("txtSearch")!;

    // quick search autocomplete
    if (this.elSearch) {
      const seqKeywords = kk_globals_get("SEQ_KEYWORDS");
      const seqKanjis = kk_globals_get("SEQ_KANJIS");

      const actb1 = new actb(this.elSearch, seqKeywords);
      actb1.onChangeCallback = this.quicksearchOnChangeCallback.bind(this);
      actb1.onPressEnterCallback = this.quicksearchOnChangeCallback.bind(this);

      actb1.actb_extracolumns = function (iRow) {
        return `<span class="f">${
          iRow + 1
        }</span><span class="k cj-k" lang="ja" xml:lang="ja">&#${seqKanjis.charCodeAt(
          iRow
        )};</span>`;
      };

      // clicking in quick search box selects the text
      $$(this.elSearch).on("focus", (evt: Event) => {
        if (this.elSearch.value !== "") {
          this.elSearch.select();
        }
      });
    }

    // auto focus search box
    if (this.elSearch && this.elSearch.value === "") {
      this.elSearch.focus();
    }

    const elEditStory = domGetById("JsEditStoryInst")!;
    if (elEditStory) {
      let { vm } = VueInstance(
        KoohiiEditStory,
        elEditStory,
        kk_globals_get("EDITSTORY_PROPS")
      );
      window.Koohii.Refs.vueEditStory = vm;
    }

    const elLastViewed = domGetById("JsLastViewedKanji");
    if (elLastViewed) {
      VueInstance(KoStudyLastViewed, elLastViewed);
    }

    const elDictStudy = domGetById("DictStudy");
    elDictStudy && this.initDictionary(elDictStudy);

    const elSharedStories = domGetById("SharedStoriesComponent");
    if (elSharedStories) {
      new SharedStoriesComponent(elSharedStories);
    }

    const elEditFlashcard = domGetById("EditFlashcard");
    if (elEditFlashcard) {
      this.elEditFlashcard = elEditFlashcard;
      (new EventDelegator(elEditFlashcard))
        .on("click", ".JsEditFlashcard", this.onEditFlashcard.bind(this));
    }
  },

  initDictionary(el: Element) {
    $$("#DictHead").on("click", this.toggleDictionary.bind(this));
    this.dictVisible = false;
    this.dictPanel = false;
  },

  toggleDictionary(evt: Event) {
    const visible = !this.dictVisible;
    const $elBody = $$<HTMLElement>("#JsDictBody");

    $elBody.display(visible);
    this.dictVisible = visible;

    if (!this.dictPanel) {
      // use inner div set in the php template
      let elMount = $elBody.down(".JsMount")[0];
      let { vm } = VueInstance(KoohiiDictList, elMount);
      let ucsId = parseInt($elBody[0].dataset.ucs!);
      (vm as TVueKoohiiDictList).load(ucsId);

      this.dictPanel = true;
    }
  },

  onEditFlashcard(evt: Event, el: Element) {
    let data = (el as HTMLElement).dataset;

    function onMenuResponse(result: "added" | "deleted") {
      // update icon to reflect new flashcard state
      let z = { added: "1", deleted: "0" };
      if (z.hasOwnProperty(result)) {
        let div = el.parentElement!;
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
        data.uri!,
        JSON.parse(data.param!),
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
      (this.oEditFlashcard as any).show();
    }

    return false;
  },

  /**
   * Update Jan 2023
   *
   *   Always lookup by kanji, and use /study/kanji/{x} URLs (fixes #288)
   *
   *  - autocomplete.js no longer returns a keyword, always a kanji
   *  - if user enters a unicode like `19968`, convert it to kanji where possible
   *
   */
  quicksearchOnChangeCallback(search: string) {
    const frameNum = search.trim();
    let char: string | null = "";
    let matches: RegExpExecArray | null;

    // if it's an integer, assume it is an Heisig index or decimal Unicode
    if (/^\d+$/.test(frameNum)) {
      char = RTK.getCharForIndex(parseInt(frameNum));
    }

    if (!char && (matches = /([\u4e00-\u9fff])/.exec(search))) {
      // if it is text and it has kanji in it, use the 1st kanji as the search
      // Regexp is equivalent of \p{InCJK_Unified_Ideographs}
      char = matches[1];
    }

    // if it isn't a sequence nr, or a UCS, let it pass through as is
    char = char || search;

    if (char) {
      window.location.href = kk_globals_get("STUDY_SEARCH_URL") + "/" + char;

      return true;
    }
  },
};
