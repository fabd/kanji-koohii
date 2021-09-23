<template>
  <div>
    <div ref="refLoadingMask" class="dict-panel">
      <template v-if="isLoading">
        <div style="min-height: 100px"></div>
      </template>
      <template v-else-if="items.length">
        <div class="dict-list">
          <template v-for="$item in items" :key="$item.id">
            <div
              :class="['dl_item', { 'dl_item--pick': $item.pick }]"
              @click="onVocabPick($item)"
            >
              <div class="dl_t">
                <div v-if="!!KanjiReview" class="dl_t_menu">
                  <i v-if="$item.pick === true" class="fa fa-star"></i>
                  <i v-else class="far fa-star"></i>
                </div>

                <cjk-lang-ja
                  class-name="c vocab_c"
                  :html="$item.c"
                  :class="{ known: $item.known }"
                ></cjk-lang-ja>
                <cjk-lang-ja
                  class-name="r vocab_r"
                  :html="$item.fr"
                ></cjk-lang-ja>
              </div>
              <div class="dl_d">
                {{ $item.g }}
              </div>

              <div if="isMenu"> </div>
            </div>
          </template>
        </div>
      </template>
      <template v-else class="dict-list_info">
        <!-- items.length === 0 -->
        <p>There are no common words using this character.</p>
      </template>
    </div>

    <!-- (legacy code) "Close" button for mobile portait will be handled by KoohiiDialog if/when we implement that -->
    <div v-if="isMobile" class="uiBMenu">
      <div class="uiBMenuItem">
        <a class="uiFcBtnGreen JSDialogHide uiIBtn uiIBtnDefault" href="#"
          ><span>Close</span></a
        >
      </div>
    </div>
  </div>
</template>

<script lang="ts">
/**
 * The dictionary list as seen in Study pages, and dictionary lookup in flashcard reviews.
 *
 * In the future, this list may allow to toggle bookmarking any individual entry, creating
 * a list of vocabulary for the user.
 *
 */
import { defineComponent } from "vue";
import { DictId, DictListEntry, GetDictListForUCS } from "@app/api/models";
import { getApi } from "@app/api/api";
import { kkFormatReading } from "@lib/format";

import KanjiReview from "@app/review/review-kanji";
import CjkLangJa from "@/vue/CjkLangJa.vue";
import KoohiiLoading from "@/vue/KoohiiLoading";
import KoohiiFlashcardKanji from "./KoohiiFlashcardKanji.vue";

// our simple regexp matching needs this so that vocab with okurigana is considered known
const HIRAGANA =
  "ぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをんゔゕゖ ゙ ゚゛゜ゝゞゟ";
const KATAKANA =
  "゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ";
const PUNCTUATION =
  "｟｠｡｢｣､･ｦｧｨｩｪｫｬｭｮｯｰｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜﾝﾞ";

// cf. rtkLabs.php (this will go into an include)
/*
const PRI_ICHI1 = 0X80
const PRI_NEWS1 = 0X40
const PRI_NEWS2 = 0X20
const PRI_ICHI2 = 0X10
const PRI_SPEC1 = 8
const PRI_SPEC2 = 4
const PRI_GAI1  = 2
const PRI_GAI2  = 1
*/

export default defineComponent({
  name: "KoohiiDictList",

  components: {
    CjkLangJa,
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
    this.items;
    this.isLoading = true;
  },

  beforeUnmount() {
    console.log("KoohiiDictList::beforeUnmount()");
  },

  methods: {
    // ! CAN NOT be computed because the dictionary is instanced *once*, while flashcard comp is recreated
    //
    getKanjiCard(): TVueInstanceOf<typeof KoohiiFlashcardKanji> | null {
      if (!this.KanjiReview) {
        return null;
      }
      let vmFlashcard = this.KanjiReview!.oReview!.getFlashcard();
      let inst = vmFlashcard.getChild() as TVueInstanceOf<
        typeof KoohiiFlashcardKanji
      >;
      return inst;
    },

    onVocabPick(item: DictListEntry) {
      console.log('onVocabPick "%s"', item.c);

      if (!this.KanjiReview) {
        return;
      }

      // add
      if (item.pick !== true) {
        KoohiiLoading.show({
          target: this.$refs.refLoadingMask as HTMLElement,
        });

        getApi()
          .legacy.setVocabForCard(this.ucsId, item.id)
          .then((tron) => {
            KoohiiLoading.hide();
            // success:  show vocab onto the flashcard, and close the dictionary
            tron.isSuccess() && this.onVocabPickResponse(item);
          });
      }
      // remove
      else {
        KoohiiLoading.show({
          target: this.$refs.refLoadingMask as HTMLElement,
        });

        getApi()
          .legacy.deleteVocabForCard(this.ucsId)
          .then((tron) => {
            KoohiiLoading.hide();
            tron.isSuccess() && this.onVocabDeleteResponse(item);
          });
      }
    },

    onVocabDeleteResponse(item: DictListEntry) {
      item.pick = false;
      this.getKanjiCard()!.removeVocab(item);
      this.KanjiReview && this.KanjiReview.toggleDictDialog();
    },

    /**
     * @param item  One of this.items[] which was clicked
     */
    onVocabPickResponse(item: DictListEntry) {
      // sets highlighted entry
      this.items.forEach((o) => {
        o.pick = false;
      });
      item.pick = true;

      const VocabPick = {
        compound: item.c,
        reading: item.r,
        gloss: item.g,
      };
      this.getKanjiCard()!.setVocab(VocabPick);

      this.KanjiReview && this.KanjiReview.toggleDictDialog();
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

        getApi()
          .legacy.getDictListForUCS(ucsId, true !== this.isSetKnownKanji)
          .then((tron) => {
            KoohiiLoading.hide();
            tron.isSuccess() && this.onDictLoadResponse(ucsId, tron.getProps());
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

      let items = this.setKnownItems(props.items, this.knownKanji);

      this.formatDictEntryArray(items);
      this.applyVocabPicks(items, props.picks);

      this.items = items;

      this.isLoading = false;
    },

    setKnownItems(items: DictListEntry[], knownKanji: string): DictListEntry[] {
      // if (this.knownKanji !== '') {
      //   console.log(' knownKanji : ' + this.knownKanji)
      // }
      const KNOWN_KANJI = knownKanji + HIRAGANA + KATAKANA + PUNCTUATION;

      // a basic string search could be faster - it's a very small list though
      const regexp = new RegExp("^[" + KNOWN_KANJI + "]+$");
      items.forEach((item) => {
        item.known = regexp.test(item.c);
      });

      // sort known vocab first
      let knownItems = items.filter((o) => o.known === true);
      let unkownItems = items.filter((o) => o.known === false);
      let sortedItems = knownItems.concat(unkownItems);

      return sortedItems;
    },

    // assign a "formatted reading" for display, keep DictEntry's reading
    formatDictEntryArray(items: DictListEntry[]) {
      items.forEach((o) => {
        o.fr = kkFormatReading(o.r);
      });
    },

    // set selected state, where 'picks' is an array of dictid's
    applyVocabPicks(items: DictListEntry[], picks: DictId[]) {
      items.forEach((o) => {
        o.pick = picks.includes(o.id);
      });
    },

    // sortSelectedItems(items)
    // {
    //   let picks = items.filter(o => o.pick === true)
    //   let other = items.filter(o => o.pick !== true)  // undefined
    //   return picks.concat(other)
    // }
  },
});
</script>

<style lang="scss">
@import "@/assets/sass/components/DictList.scss";
</style>
