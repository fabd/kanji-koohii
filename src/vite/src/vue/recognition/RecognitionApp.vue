<template>
  <div>
    <h2>Kanji Recognition</h2>

    <div class="flex">
      <!-- ------------------------------------------------------ -->
      <!-- MAIN COL -->
      <!-- ------------------------------------------------------ -->
      <div class="flex-1">
        <div v-if="isStateEdit" class="mb-4">
          <p>
            Copy and paste japanese text into the form below, then click "Show".
            The kanji for which you have flashcards will be
            <u>hyperlinked</u> to the Study pages, and a popup will reveal the
            Heisig keywords.</p
          >

          <form class="mb-4">
            <textarea
              ref="input"
              v-model="japaneseText"
              class="w-full mb-2 p-2 min-h-[300px] border border-[#ddd] rounded-lg text-lg"
            ></textarea>
            <input
              type="submit"
              class="ko-Btn ko-Btn--success"
              value="Show"
              @click.prevent="onClickShow"
            />
          </form>
        </div>

        <div v-if="!isStateEdit">
          <!-- ------------------------------------------------------ -->
          <!-- OUTPUT -->
          <!-- ------------------------------------------------------ -->
          <div class="ko-Recognition-output mb-8">
            <cjk-lang-ja>
              <template v-for="(k, i) in jtextarray" :key="i">
                <div
                  v-if="k.heisigNr"
                  class="ko-Recognition-k"
                  :class="{
                    'is-known': k.isKnown,
                    'is-active': i === curKanjiIndex,
                  }"
                  @click="onClickCharacter(k, i)"
                >
                  <span>{{ k.kanji }}</span>
                </div>
                <div v-else class="ko-Recognition-u">
                  <span>{{ k.kanji }}</span>
                </div>
              </template>
            </cjk-lang-ja>
          </div>

          <div class="mb-4 pb-4 border-b border-[#d4cdba]">
            <button class="ko-Btn ko-Btn--success" @click="onClickToEdit">{{
              "Enter more japanese text"
            }}</button>
          </div>
        </div>
      </div>
      <!-- ------------------------------------------------------ -->
      <!-- SIDE COL -->
      <!-- ------------------------------------------------------ -->
      <div class="w-[400px] ml-4">
        <template v-if="isStateEdit">
          <div class="ko-RecognitionPane">
            <div id="introduction" class="markdown">
              <h3>Instructions</h3>
              <p> Enter Japanese text to the left... </p>

              <h3>Purpose of this page</h3>

              <p>
                In this reading section, you can test your memory from the kanji
                to the keyword, while also seeing kanji in context.
              </p>

              <p>
                See if you can recognize simple words made of kanji you already
                know. You may also be able to guess the meaning of some of these
                words based on the meaning of the characters.
              </p>

              <h3>Resources</h3>

              <ul>
                <li>
                  Japanese text:
                  <a href="https://www.aozora.gr.jp/" target="_blank"
                    >Aozora Bunko</a
                  >.
                </li>
                <li>
                  <a
                    href="https://www.kanji.org/kanji/japanese/writing/outline.htm"
                    target="_blank"
                    >Guide to the Japanese Writing System</a
                  >
                  by Jack Halpern
                </li>
              </ul>
            </div>
          </div>
        </template>

        <template v-if="!isStateEdit && !curKanji">
          <div class="ko-RecognitionPane">
            <p class="text-md text-[#42413d]">
              Select a character on the left to display more information in this
              pane.
            </p>
          </div>
        </template>

        <template v-if="!isStateEdit && curKanji">
          <div class="ko-RecognitionPane">
            <div class="flex mb-6">
              <div class="ko-RecognitionPane-kanji">
                <span class="ko-RecognitionPane-kanjiChar">
                  {{ curKanji.kanji }}
                </span>
              </div>

              <div v-if="isKana(curKanji.kanji)" class="ml-5">
                <div class="mb-4">
                  <h3 class="ko-RecognitionPane-h3 mb-0">KANA - READING</h3>
                  <div
                    class="font-serif italic text-[#42413d] text-[34px] leading-none"
                    >{{ toRomaji(curKanji.kanji) }}</div
                  >
                </div>

                <div class="mb-4">
                  <h3 class="ko-RecognitionPane-h3 mb-0">UNICODE POINT</h3>
                  <div class="text-[#42413d] text-md leading-none">
                    <span>{{ curKanji.kanji.charCodeAt(0) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="isKanji(curKanji.kanji)" class="ml-5">
                <div class="mb-4">
                  <h3 class="ko-RecognitionPane-h3 mb-0">Keyword</h3>
                  <div
                    class="font-serif italic text-[#42413d] text-[34px] leading-none"
                  >
                    <span>{{ curKanji.keyword }}</span>
                  </div>
                </div>

                <div class="mb-4">
                  <h3 class="ko-RecognitionPane-h3 mb-0">Heisig Index</h3>
                  <div
                    class="font-serif text-[#42413d] text-[34px] leading-none"
                  >
                    <span>{{ curKanji.heisigNr }}</span>
                  </div>
                </div>

                <a :href="getStudyPageLink(curKanji.kanji)" class="block"
                  >Go to study page</a
                >
              </div>
            </div>

            <div v-if="!isHiragana(curKanji.kanji)">
              <h3 class="ko-RecognitionPane-h3 mb-2">Dictionary</h3>

              <!-- ------------------------------------------------------ -->
              <!-- DICT -->
              <!-- ------------------------------------------------------ -->
              <div ref="refLoadingMask" class="ko-RecognitionPane-dict">
                <template v-if="dictLoading">
                  <div style="min-height: 100px"></div>
                </template>
                <template v-else-if="dictItems.length">
                  <dict-list
                    ref="refDictList"
                    :items="dictItems"
                    :known-kanji="knownKanji"
                  />
                </template>
                <div v-else class="bg-[#fff] mx-2 rounded-sm">
                  <ko-dict-empty :ucs-id="curKanji.ucsId" />
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
// @ts-nocheck ( unfinished feature, silence vue-tsc )
import { defineComponent } from "vue";
import { kk_globals_get } from "@app/root-bundle";
import { urlForStudy } from "@/lib/koohii";
import * as wanakana from "wanakana";
import * as CJK from "@/lib/cjk";
import * as RTK from "@/lib/rtk";
import CacheDictResults from "@/app/dict/CacheDictResults";

import CjkLangJa from "@/vue/CjkLangJa.vue";
import DictList from "@/vue/DictList.vue";
import KoDictEmpty from "@/vue/KoDictEmpty.vue";

const DEFAULT_TEXT = `むかし、むかし、ご存知のとおり、うさぎとかめは、山の上まで競争しました。誰もが、うさぎの方がかめよりも早くそこに着くと思いました。しかし迂闊にも、うさぎは途中で寝てしまいました。目が覚めた時は、もうあとのまつりでした。かめはすでに山のてっ辺に立っていました。`;

type TRecKanji = {
  kanji: string; // single kanji
  ucsId: TUcsId; // ucs code
  heisigNr: TUcsId; // heisig index nr OR ucs code for non-Heisig chars
  keyword: string;
  url?: string;
  isKnown?: boolean;
};

// --------------------------------------------------------------------
// hydration
// --------------------------------------------------------------------

const knownKanji = kk_globals_get("USER_KNOWN_KANJI") as string;
const isKnownKanji = (char: string) => knownKanji.indexOf(char) >= 0;

// --------------------------------------------------------------------

export default defineComponent({
  name: "RecognitionApp",

  components: {
    CjkLangJa,
    DictList,
    KoDictEmpty,
  },

  data() {
    return {
      japaneseText: DEFAULT_TEXT,

      jtextarray: [] as TRecKanji[],

      isStateEdit: true,

      curKanji: null as TRecKanji | null,
      curKanjiIndex: -1,

      // cur kanji info
      kanji: "思",
      heisigIndex: 651,
      keyword: "think",

      // dictionary
      dictLoading: false,
      dictItems: [] as DictListEntry[],
      knownKanji: "",
    };
  },

  mounted() {
    this.isStateEdit && this.focusInput();
  },

  beforeMount() {
    // testing
    // this.onClickShow();
    // this.curKanjiIndex = 10;
    // this.curKanji = this.jtextarray[this.curKanjiIndex];
    // let s, ss;
    // console.log("k", (s = CJK.getKanji(DEFAULT_TEXT)), s.length);
    // console.log("kk", (ss = CJK.getUniqueKanji(DEFAULT_TEXT)), ss.length);
  },

  methods: {
    // proxies
    isKana: wanakana.isKana,
    isKanji: wanakana.isKanji,
    isHiragana: wanakana.isHiragana,
    toRomaji: wanakana.toRomaji,

    focusInput() {
      (this.$refs.input as HTMLElement).focus();
    },

    getStudyPageLink(strKanji: string) {
      return urlForStudy(strKanji);
    },

    onClickCharacter(charData: TRecKanji, index: number) {
      this.curKanji = charData;
      this.curKanjiIndex = index;

      const setDictItems = (items: DictResults) => {
        console.log("set dict items %o", items);
        this.dictItems = items;

        // this.dictItems = [
        //   { id: 19968, c: "@", r: "reading", g: "gloss", pri: 666, },
        //   { id: 19968, c: "@", r: "reading", g: "gloss", pri: 666, },
        //   { id: 19968, c: "@", r: "reading", g: "gloss", pri: 666, },
        //   { id: 19968, c: "@", r: "reading", g: "gloss", pri: 666, },
        // ] as DictResults;
      };

      //
      let CDR = CacheDictResults.getInstance();
      let ucsId = charData.ucsId;
      let results = CDR.getResultsForUCS(ucsId);
      console.log("CDR results", results);

      if (!results) {
        CDR.cacheResultsFor(charData.kanji, (items) => {
          let results = CDR.getResultsForUCS(ucsId);
          console.assert(!!results); // should be cached now, always
          this.dictItems = results!;
        });
      } else {
        setDictItems(results);
      }
    },

    onClickShow() {
      this.isStateEdit = false;

      this.parseText();
    },

    onClickToEdit() {
      this.isStateEdit = true;
      this.curKanji = null;
      this.curKanjiIndex = -1;

      this.$nextTick(() => {
        this.focusInput();
      });
    },

    parseText() {
      let out: TRecKanji[] = [];

      for (let char of this.japaneseText) {
        let ucsId = char.charCodeAt(0);
        // console.log(strKanji, kanjiInfo);

        const heisigNr = RTK.getIndexForUCS(ucsId);
        const keyword = heisigNr ? RTK.getKeywordForUCS(ucsId) : "";

        const data: TRecKanji = {
          kanji: char,
          ucsId: char.charCodeAt(0),
          heisigNr: heisigNr,
          keyword: keyword,
        };

        if (isKnownKanji(char)) {
          data.isKnown = true;
          data.url = `study/kanji/${ucsId}`;
        }

        out.push(data);
      }

      this.jtextarray = out;
    },
  },
});
</script>
