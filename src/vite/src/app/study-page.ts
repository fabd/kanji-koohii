// FIXME: this is the legacy study-page.js, should be a master Vue component someday

import $$, { domGetById } from "@lib/dom";
import { kk_globals_get } from "@app/root-bundle";
import * as RTK from "@/lib/rtk";
import { getApi } from "@app/api/api";
import { baseUrl } from "@/lib/koohii";

import VueInstance from "@lib/helpers/vue-instance";

import actb from "@old/autocomplete.js";

import EventDelegator from "@lib/EventDelegator";
import EditFlashcardDialog from "@old/components/EditFlashcardDialog";
import KoohiiDictList from "@/vue/KoohiiDictList.vue";
import KoohiiEditStory from "@/vue/KoohiiEditStory.vue";
import KoStudyLastViewed from "@/vue/KoStudyLastViewed.vue";
import SharedStoriesComponent from "@old/components/SharedStoriesComponent";

type TVueKoohiiDictList = TVueInstanceOf<typeof KoohiiDictList>;

class KoNotification {
  private element: HTMLElement;
  private static hideTimeout: NodeJS.Timeout | null = null;

  constructor() {
    // Create the div only once
    let div = $$<HTMLElement>(".ko-Notification")[0];
    if (!div) {
      div = document.createElement("div");
      div.className = "ko-Notification";
      document.body.appendChild(div);
    }

    this.element = div;
  }

  public show(message: string): void {
    // Clear any existing timeout
    if (KoNotification.hideTimeout) {
      clearTimeout(KoNotification.hideTimeout);
    }

    // Set the message and show the notification
    this.element.textContent = message;
    this.element.classList.remove("hide");

    // Trigger reflow to ensure the transition works
    void this.element.offsetWidth;

    this.element.classList.add("show");

    // Hide after 3 seconds
    KoNotification.hideTimeout = setTimeout(() => {
      this.hide();
    }, 3000);
  }

  public hide(): void {
    this.element.classList.add("hide");
  }
}

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

  elSearch: null as any as HTMLInputElement,

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

    const elEditFlashcard = $$<HTMLElement>(".ko-EditFlashcard")[0];
    if (elEditFlashcard) {
      this.initFlashcardButton(elEditFlashcard);
    }
  },

  initFlashcardButton(el: HTMLElement) {
    this.elEditFlashcard = el;

    this.newCount = kk_globals_get("NEW_CARDS_COUNT");

    cardData = kk_globals_get("STUDY_FLASHCARD");

    this.renderFlashcardButton(hasCard(), false);

    new EventDelegator(el).on(
      "click",
      ".JsEditFlashcard",
      this.onClickFlashcardButton.bind(this)
    );
  },

  renderFlashcardButton(state: boolean, loading: boolean) {
    let html = "";

    // the new cards indicator
    if (this.newCount) {
      html = `
<button class="ko-Btn ko-StudyNewCount tooltip" data-tooltip="New cards" tabindex="0"><i></i>${this.newCount}</button>
    `;
    }

    // the Add/Edit Flashcard button
    if (loading) {
      html += `
<a href="#" class="uiGUI ko-Btn JsEditFlashcard is-loading">
  <div class="flex items-center justify-center">
    <div class="is-icon spinner mr-2"></div>
  </div>
</a>`;
    } else if (state === false) {
      html += `
<a href="#" class="uiGUI ko-Btn ko-Btn--success JsEditFlashcard" title="Add Card">
  <div class="is-icon fa fa-plus mr-2"></div>Add Card
</a>`;
    } else if (state === true) {
      html += `
<a href="#" class="uiGUI ko-Btn is-ghost rounded-sm JsEditFlashcard" title="Edit Card">
  <div class="is-icon fa fa-edit mr-2"></div>Edit Card
</a>`;
    }

    this.elEditFlashcard!.innerHTML = html;
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
      let { vm } = VueInstance(KoohiiDictList, elMount!);
      let ucsId = parseInt($elBody[0]!.dataset.ucs!);
      (vm as TVueKoohiiDictList).load(ucsId);

      this.dictPanel = true;
    }
  },

  onClickFlashcardButton(evt: Event, el: Element) {
    const onMenuResponse = (result: "added" | "deleted") => {
      // reset button to "Add Card" state
      if (result === "deleted") {
        // if it was a new card that was deleted, update the count
        if (isNewCard()) {
          this.newCount--;
        }

        this.renderFlashcardButton(false, false);

        // update the state
        cardData = null;
      }
    };

    // the current kanji
    const ucsId = kk_globals_get("LASTVIEWED_UCS_ID");

    if (!hasCard()) {
      // set loading state for the button
      this.renderFlashcardButton(false, true);

      // make sure the loading state shows half a sec for visual feedback
      const startTime = Date.now();
      const minDelay = 500;

      getApi()
        .legacy.addCard(ucsId)
        .then((tron) => {
          const elapsed = Date.now() - startTime;
          const remainingDelay = Math.max(0, minDelay - elapsed);

          // Wait for the remaining time if needed
          setTimeout(() => {
            if (tron.isSuccess()) {
              // update the new cards count before rendering
              this.newCount++;

              this.renderFlashcardButton(true, false);

              // update the state for the flashcard
              cardData = {
                ucsId: ucsId,
                leitnerBox: 1,
                totalReviews: 0,
              };

              let notif = new KoNotification();
              notif.show("Flashcard added");
            }
          }, remainingDelay);
        })
        .catch(() => {
          // cancel loading state
          this.renderFlashcardButton(false, false);
        });
    } else if (!this.oEditFlashcard) {
      this.oEditFlashcard = new EditFlashcardDialog(
        `${baseUrl()}/flashcards/dialog`,
        { ucs: ucsId },
        [this.elEditFlashcard, "tr", "br"],
        {
          events: {
            onMenuResponse: onMenuResponse,
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
