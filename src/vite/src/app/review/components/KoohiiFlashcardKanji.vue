<template>
  <div
    class="h-full"
    :class="{
      'with-yomi': hasVocab,
    }"
  >
    <template v-if="!reviewMode.freemode">
      <a
        id="uiFcMenu"
        href="#"
        title="Edit Flashcard"
        class="uiGUI uiFcAction"
        data-action="JSFcMenu"
        ><i class="fa fa-bars"></i
      ></a>
    </template>

    <div class="d-strokec" title="Stroke count">
      <cjk-lang-ja html="&#30011;&#25968;" class="kanji" />
      <span>{{ cardData.strokecount }}</span>
    </div>

    <div class="d-framenr" v-html="cardData.framenum"></div>

    <!-- col layout [ keyword / ( kanji vocab ) ] -->
    <div class="flex flex-nowrap flex-col h-full">
      <div class="d-keyword flex-grow-0">
        <div
          v-if="cardData.isAgain"
          class="text-[#aeaeae] text-md leading-none inline-block"
          ><i class="fa fa-redo mr-2"></i
        ></div>
        <a
          :href="`/study/kanji/${cardData.kanji}`"
          title="Go to the Study page"
          target="blank"
          class="JsLink mr-2"
          >{{ cardData.keyword }}</a
        >
      </div>

      <!-- body of the card [  kanji  /  vocab  ] -->
      <div
        class="fc-middle pb-[40px] h-full flex flex-col md:flex-row flex-nowrap flex-1"
      >
        <div class="uiFcHalf d-kanji flex">
          <!-- the kanji is fully centered (flex child auto margin trick) -->
          <cjk-lang-ja class="d-kanji-char m-auto relative" :html="cardData.kanji" />
        </div>

        <div v-if="hasVocab" class="uiFcHalf d-yomi">
          <div class="px-2">
            <transition name="uiFcYomi-fadein" appear>
              <div>
                <div
                  v-for="$item in vocab"
                  :key="$item.dictid"
                  class="uiFcYomi"
                  @click.stop="onVocabClick"
                >
                  <div :class="textSizeForText($item.compound)">
                    <cjk-lang-ja
                      class="vocab_c text-[1.5em] inline-block"
                      :html="formatCompound($item.compound)"
                    ></cjk-lang-ja>
                    <cjk-lang-ja
                      class="vocab_r text-[1em] whitespace-nowrap ml-2"
                      :html="formatReading($item.reading)"
                    ></cjk-lang-ja>
                  </div>
                  <div
                    class="vyg text-[#858280] italic text-xl leading-[1.2em] pl-3 max-sm:ml-2 mt-4"
                  >
                    {{ $item.gloss }}
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import CjkLangJa from "@/app/common/components/CjkLangJa.vue";
import { kkFormatReading } from "@/lib/format";

export default defineComponent({
  name: "KoohiiFlashcardKanji",

  components: {
    CjkLangJa,
  },

  data() {
    return {
      /**
       * Array of VocabPick   (a subset of DictEntry's structure, as used on the flashcard)
       *
       *   compound    "欠如"
       *   reading     "けつ(じょ)"
       *   gloss
       *
       */
      vocab: [],
    };
  },

  computed: {
    reviewMode() {
      // console.log('get reviewMode()')
      return this.$parent.reviewMode;
    },

    cardData() {
      // console.log('get cardData()')
      return this.$parent.cardData;
    },

    hasVocab() {
      return this.vocab.length > 0;
    },
  },

  beforeMount() {
    // console.log('KoohiiFlashcardKanji::beforeMount()')

    const VocabPickArray = this.cardData.vocab || [];

    // VocabPickArray.forEach((o) => { o.r =  })

    // assign the card's DictEntryArray
    this.vocab = VocabPickArray;
  },

  methods: {
    onVocabClick() {
      // console.log('onVocabClick()')
      Koohii.Refs.KanjiReview.toggleDictDialog();
    },

    // @param {object} DictEntry    { compound, reading, gloss }, cf. rtkLabs.php
    setVocab(DictEntry) {
      // console.log('setVocab(%o)', DictEntry)
      const item = DictEntry;

      // do it this way so the "enter" transition plays again when changing an existing item
      this.clearVocab();
      this.$nextTick(() => {
        this.vocab.push(item);
        this.updateSourceCard(this.vocab);
      });
    },

    removeVocab(_item) {
      // console.log('removeVocab(%o)', item)
      this.clearVocab();
      this.updateSourceCard(this.vocab);
    },

    clearVocab() {
      this.vocab.splice(0);
    },

    // update the source, so going backward with "Undo" is consistent with any changes
    updateSourceCard(DictEntryArray) {
      this.cardData.vocab = DictEntryArray;
    },

    textSizeForText(text) {
      return text.length > 4 ? "text-xl" : "text-2xl";
    },

    // format compound depending on card side
    formatCompound(str) {
      const isFront = this.$parent.getState() === 0;
      const kanji = this.cardData.kanji;

      // console.log("formatCompound %s %s %o", str, kanji, isFront);

      if (isFront) {
        str = str.replace(kanji, '<span class="cloze"><u>' + kanji + "</u></span>");
      }

      return str;
    },

    /**
     * @param {string} kana
     * @returns string
     */
    formatReading(kana) {
      return kkFormatReading(kana);
    },
  },
});
</script>
