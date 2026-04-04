// this is the legacy study-page.js, could be a master Vue component someday

import $$, { domGetById } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import * as RTK from "@/lib/rtk";
import { getApi } from "@app/api/api";
import eventBus from "@/lib/EventBus";
import EventDelegator from "@lib/EventDelegator";
import EditFlashcardDialog from "@old/components/EditFlashcardDialog";
import KoohiiDictList from "@/vue/KoohiiDictList.vue";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";
import KoStudyLastViewed from "@/vue/KoStudyLastViewed.vue";
import SharedStoriesComponent from "@old/components/SharedStoriesComponent";
import VueInstance from "@lib/helpers/vue-instance";
import AutoComplete from "@/components/KoStudySearch";

let cardData: Window["KK"]["STUDY_FLASHCARD"] = null;

function hasCard() {
  return cardData !== null;
}

function isNewCard() {
  return cardData && cardData.leitnerBox === 1 && cardData.totalReviews === 0;
}

export default {
  elEditFlashcard: null as HTMLElement | null,
  newCount: 0,
  oEditFlashcard: null as EditFlashcardDialog | null,
  resetFlashcardDialog: false,
  dictVisible: false,
  dictPanel: false,

  initialize() {
    const elStudySearch = $$<HTMLElement>(".ko-StudySearch")[0];

    // search autocomplete
    if (elStudySearch) {
      elStudySearch.innerHTML = `
<input type="text" name="search" value="" class="form-control" maxlength="32" id="txtSearch" placeholder="Enter number, kanji or keyword" autocomplete="off" />
<ul class="ko-StudySearchDD absolute hidden overflow-hidden"></ul>
      `;

      const elInput = elStudySearch.querySelector("input")!;
      const elDropdown = elStudySearch.querySelector("ul")!;

      console.assert(elInput !== null && elDropdown !== null);

      new AutoComplete({
        inputElement: elInput,
        dropdownElement: elDropdown,
        keywords: kk_globals_get("SEQ_KEYWORDS"),
        kanjis: kk_globals_get("SEQ_KANJIS"),
        maxResults: 10,
        onSelect: (word: string) => {
          this.onSearch(word);
        },
      });

      // clicking in the search box selects the text
      $$(elInput).on("focus", (_evt: Event) => {
        if (elInput.value !== "") {
          elInput.select();
        }
      });

      // auto focus search box
      if (elInput.value === "") {
        elInput.focus();
      }
    }

    const elEditStory = domGetById("JsEditStoryInst")!;
    if (elEditStory) {
      const { vm } = VueInstance(
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
    if (elDictStudy) {
      this.initDictionary(elDictStudy);
    }

    const elSharedStories = domGetById("SharedStoriesComponent");
    if (elSharedStories) {
      new SharedStoriesComponent(elSharedStories);
    }

    const elEditFlashcard = $$<HTMLElement>(".ko-EditFlashcard")[0];
    if (elEditFlashcard) {
      this.initFlashcardButton(elEditFlashcard);
      eventBus.connect("kk.flashcard.deleted", this.onDeleteFlashcard, this);
      eventBus.connect("kk.flashcard.restudy", this.onRestudyFlashcard, this);
    }
  },

  initFlashcardButton(el: HTMLElement) {
    this.elEditFlashcard = el;

    this.newCount = kk_globals_get("NEW_CARDS_COUNT");

    cardData = kk_globals_get("STUDY_FLASHCARD");

    this.renderFlashcardButton(hasCard() ? "edit" : "add");

    new EventDelegator(el).on(
      "click",
      ".JsEditFlashcard",
      this.onClickFlashcardButton.bind(this)
    );
  },

  renderFlashcardButton(state: "add" | "edit" | "loading" | "confirm") {
    let html = "";

    // the new cards indicator
    if (this.newCount) {
      html = `
<button class="ko-Btn ko-StudyNewCount ko-Tooltip" data-tooltip="New cards" tabindex="0"><i></i>${this.newCount}</button>
    `;
    }

    // the Add/Edit Flashcard button
    if (state === "confirm") {
      html += `
<div class="ko-Btn ko-Btn--success">
  <i class="fa fa-check mr-2"></i>Flashcard added
</div>`;
    } else if (state === "loading") {
      html += `
<button href="#" class="uiGUI ko-Btn ko-Btn--success is-loading">
  <div class="flex items-center justify-center">
    <div class="is-icon is-spinner mr-2"></div>Add Card
  </div>
</button>`;
    } else if (state === "add") {
      html += `
<button class="uiGUI ko-Btn ko-Btn--success JsEditFlashcard" title="Add Card">
  <div class="is-icon fa fa-plus mr-2"></div>Add Card
</button>`;
    } else if (state === "edit") {
      html += `
<button class="uiGUI ko-Btn is-ghost rounded-sm JsEditFlashcard" title="Edit Card">
  <div class="is-icon fa fa-edit mr-2"></div>Edit Card
</button>`;
    }

    this.elEditFlashcard!.innerHTML = html;
  },

  initDictionary(_el: Element) {
    $$("#DictHead").on("click", this.toggleDictionary.bind(this));
    this.dictVisible = false;
    this.dictPanel = false;
  },

  toggleDictionary(_evt: Event) {
    const visible = !this.dictVisible;
    const $elBody = $$<HTMLElement>("#JsDictBody");

    $elBody.display(visible);
    this.dictVisible = visible;

    if (!this.dictPanel) {
      // use inner div set in the php template
      const elMount = $elBody.down(".JsMount")[0];
      const { vm } = VueInstance(KoohiiDictList, elMount!);
      const ucsId = parseInt($elBody[0]!.dataset.ucs!);
      vm.load(ucsId);

      this.dictPanel = true;
    }
  },

  onClickFlashcardButton(_evt: Event, _el: Element) {
    _evt.preventDefault();

    // the current kanji
    const ucsId = kk_globals_get("LASTVIEWED_UCS_ID");

    if (!hasCard()) {
      // set loading state for the button
      this.renderFlashcardButton("loading");

      // make sure the loading state shows half a sec for visual feedback
      const startTime = Date.now();
      const minDelay = 500;

      getApi()
        .addCard(ucsId)
        .then((tron) => {
          const elapsed = Date.now() - startTime;
          const remainingDelay = Math.max(0, minDelay - elapsed);

          // Wait for the remaining time if needed
          setTimeout(() => {
            if (tron.isSuccess()) {
              // update the new cards count before rendering
              this.newCount++;

              this.renderFlashcardButton("confirm");
              window.setTimeout(() => {
                this.renderFlashcardButton("edit");
              }, 1500);

              // update the state for the flashcard
              cardData = {
                ucsId: ucsId,
                leitnerBox: 1,
                totalReviews: 0,
              };
            }
          }, remainingDelay);
        })
        .catch(() => {
          // cancel loading state
          this.renderFlashcardButton("add");
        });
    }

    // invalidate the dialog, reload
    if (this.resetFlashcardDialog) {
      this.resetFlashcardDialog = false;
      this.oEditFlashcard?.destroy();
      this.oEditFlashcard = null;
    }

    if (hasCard() && this.oEditFlashcard) {
      this.oEditFlashcard.show();
      return;
    }

    if (hasCard() && !this.oEditFlashcard) {
      this.oEditFlashcard = new EditFlashcardDialog(
        ucsId,
        [this.elEditFlashcard, "br", "tr"],
        false
      );
    }
  },

  onDeleteFlashcard() {
    this.resetFlashcardDialog = true;

    // deleted the new card, update new card count
    if (isNewCard()) {
      this.newCount--;
    }

    // reset button to "Add Card" state
    this.renderFlashcardButton("add");
    cardData = null;
  },

  onRestudyFlashcard() {
    this.resetFlashcardDialog = true;

    // if it was a new card that moved to restudy pile, update new card count
    if (isNewCard()) {
      this.newCount--;
    }

    // update card state
    if (cardData) {
      cardData.leitnerBox = 1;
      cardData.totalReviews = 1; // isNewCard() is false
    }

    // FIXME - just want to update the New Cards indicator
    this.renderFlashcardButton("edit");

    // reload the dialog contents
    this.resetFlashcardDialog = true;
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
  onSearch(search: string) {
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
      char = matches[1] || null;
    }

    // if it isn't a sequence nr, or a UCS, let it pass through as is
    char = char || search;

    if (char) {
      window.location.href = kk_globals_get("STUDY_SEARCH_URL") + "/" + char;

      return true;
    }
  },
};
