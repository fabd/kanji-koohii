<template>
  <div>
    <div ref="refLoadingMask" class="dict-panel">
      <template v-if="isLoading">
        <div style="min-height: 100px"></div>
      </template>
      <template v-else-if="items.length">
        <dict-list
          ref="refDictList"
          :items="items"
          :known-kanji="knownKanji"
          :selected-items="picks"
          :show-selected="!!KanjiReview"
          @click="onVocabSelect($event)"
        />
      </template>
      <template v-else>
        <ko-dict-empty :ucs-id="ucsId" />
      </template>
    </div>

    <!-- (legacy code) "Close" button for mobile portait will be handled by KoohiiDialog if/when we implement that -->
    <div v-if="isMobile" class="uiBMenu">
      <div class="uiBMenuItem">
        <a class="uiFcBtnGreen JSDialogHide uiIBtn uiIBtnDefault" href="#">
          <span>Close</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/**
 * DictList with on-demand loading of entries as used by Study & Flashcard Review pages.
 *
 */
import { defineComponent } from "vue";
import type { GetDictListForUCS } from "@app/api/models";
import { getApi } from "@app/api/api";
// import CacheDictResults from "@/app/dict/CacheDictResults";

import DictList from "@/vue/DictList.vue";
import KanjiReview from "@app/review/review-kanji";
import KoDictEmpty from "@/vue/KoDictEmpty.vue";
import KoohiiLoading from "@/vue/KoohiiLoading";
import KoohiiFlashcardKanji from "./KoohiiFlashcardKanji.vue";

type TVueDictList = TVueInstanceOf<typeof DictList>;
type TVueKoohiiFlashcard = TVueInstanceOf<typeof KoohiiFlashcardKanji>;

export default defineComponent({
  name: "KoohiiDictList",

  components: {
    DictList,
    KoDictEmpty,
  },

  data() {
    return {
      isLoading: true,

      /**
       * Array of dict entries as obtained from dict lookup cache.
       *
       *   DictEntry
       *
       *     (...see data/scripts/dict/dict_gen_cache.php ...)
       *
       *     pick:       {boolean}    user selected this item
       *     known       {boolean}    contains only known kanji
       *
       */
      items: [] as DictListEntry[],

      //
      picks: [] as DictId[],

      // kanji which corresponds to the last retrieved example words (cf load())
      ucsId: 0,

      // whether we have already requested them from server
      isSetKnownKanji: false,

      // a string containing all kanji known by the user
      knownKanji: "",
    };
  },

  computed: {
    KanjiReview(): KanjiReview | undefined {
      return window.Koohii.Refs.KanjiReview;
    },

    isMobile(): boolean {
      // (legacy code) cf. lib/front/corejs/ui/mobile.js
      return window.innerWidth <= 720;
    },
  },

  created() {
    console.log("KoohiiDictList::created(%o)", this.items);
    this.isLoading = true;
  },

  beforeUnmount() {
    console.log("KoohiiDictList::beforeUnmount()");
  },

  methods: {
    getDictList(): TVueDictList {
      return this.$refs.refDictList as TVueDictList;
    },

    // ! CAN NOT be computed because the dictionary is instanced *once*, while flashcard comp is recreated
    //
    getKanjiCard(): TVueKoohiiFlashcard | null {
      if (!this.KanjiReview) {
        return null;
      }
      const vm = this.KanjiReview!.oReview!.getFlashcard()!;
      const inst = vm.getChild() as TVueKoohiiFlashcard;
      return inst;
    },

    onVocabSelect({ item, selected }: { item: DictListEntry; selected: boolean }) {
      console.log("onVocabSelect %o", item);

      if (!this.KanjiReview) return;

      KoohiiLoading.show({
        target: this.$refs.refLoadingMask as HTMLElement,
      });

      if (!selected) {
        getApi()
          .legacy.setVocabForCard(this.ucsId, item.id)
          .then((tron) => {
            KoohiiLoading.hide();
            // success:  show vocab onto the flashcard, and close the dictionary
            if (tron.isSuccess()) {
              this.onVocabPickResponse(item);
            }
          });
      } else {
        getApi()
          .legacy.deleteVocabForCard(this.ucsId)
          .then((tron) => {
            KoohiiLoading.hide();
            if (tron.isSuccess()) {
              this.onVocabDeleteResponse(item);
            }
          });
      }
    },

    setVocabPick(dictId: DictId, state: boolean) {
      if (!state) {
        this.picks = this.picks.filter((id) => id !== dictId);
      } else {
        // kiss for now, there is always only one selected vocab per ucsId
        this.picks = [dictId];
      }
    },

    onVocabDeleteResponse(item: DictListEntry) {
      this.setVocabPick(item.id, false);
      this.getKanjiCard()!.removeVocab(item);
      if (this.KanjiReview) {
        this.KanjiReview.toggleDictDialog();
      }
    },

    onVocabPickResponse(item: DictListEntry) {
      this.setVocabPick(item.id, true);
      this.getKanjiCard()!.setVocab({
        compound: item.c,
        reading: item.r,
        gloss: item.g,
      });
      if (this.KanjiReview) {
        this.KanjiReview.toggleDictDialog();
      }
    },

    load(ucsId: number) {
      this.isLoading = true;

      const doLoad = () => {
        KoohiiLoading.show({
          target: this.$refs.refLoadingMask as HTMLElement,
        });

        // getKnownKanji:
        //
        //   We request these only once for the lifetime of the component. This is more
        //   efficient in the flashcard review page.
        //
        //   The user's known kanji could realistically be 2000 to 3000 utf8 characters. So
        //   even though they are also cached in php session, it's better to avoid returning
        //   several KBs of data with each dictionary lookup request

        // let CDR = CacheDictResults.getInstance();
        // CDR.cacheResultsFor(ucsId)

        getApi()
          .legacy.getDictListForUCS(ucsId, true !== this.isSetKnownKanji)
          .then((tron) => {
            KoohiiLoading.hide();
            if (tron.isSuccess()) {
              this.onDictLoadResponse(ucsId, tron.getProps());
            }
          });
      };

      this.$nextTick(doLoad);
    },

    onDictLoadResponse(ucsId: number, props: GetDictListForUCS) {
      console.log("onDictLoadResponse(%o)", props);

      this.ucsId = ucsId;

      if (props.knownKanji) {
        this.knownKanji = props.knownKanji;
        this.isSetKnownKanji = true;
      }

      this.items = props.items;
      this.picks = props.picks;

      this.isLoading = false;
    },
  },
});
</script>
