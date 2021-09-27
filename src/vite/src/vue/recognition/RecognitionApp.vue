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
              class="btn btn-success"
              value="Show"
              @click.prevent="onClickShow"
            />
          </form>
        </div>

        <div v-if="!isStateEdit">
          <!-- ------------------------------------------------------ -->
          <!-- OUTPUT -->
          <!-- ------------------------------------------------------ -->
          <div class="kk-Recognition-output mb-8">
            <cjk-lang-ja>
              <template v-for="(k, i) in jtextarray" :key="i">
                <div
                  v-if="k.seq_nr"
                  class="kk-Recognition-k"
                  :class="{
                    'is-known': k.seq_nr,
                    'is-active': i === curKanjiIndex,
                  }"
                  @click="onClickCharacter(k, i)"
                >
                  <span>{{ k.kanji }}</span>
                </div>
                <div v-else class="kk-Recognition-u">
                  <span>{{ k.kanji }}</span>
                </div>
              </template>
            </cjk-lang-ja>
          </div>

          <div class="mb-4 pb-4 border-b border-[#d4cdba]">
            <button class="btn btn-success" @click="onClickToEdit">{{
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
          <div class="kk-RecognitionPane">
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
          <div class="kk-RecognitionPane">
            <p class="text-md text-body">
              Select a character on the left to display more information in this
              pane.
            </p>
          </div>
        </template>

        <template v-if="!isStateEdit && curKanji">
          <div class="kk-RecognitionPane">
            <div class="flex mb-6">
              <div class="kk-RecognitionPane-kanji">
                <span class="kk-RecognitionPane-kanjiChar">
                  {{ curKanji.kanji }}
                </span>
              </div>

              <div v-if="isKana(curKanji.kanji)" class="ml-5">
                <div class="mb-4">
                  <h3 class="kk-RecognitionPane-h3 mb-0">KANA - READING</h3>
                  <div
                    class="font-serif italic text-body text-[34px] leading-none"
                    >{{ toRomaji(curKanji.kanji) }}</div
                  >
                </div>

                <div class="mb-4">
                  <h3 class="kk-RecognitionPane-h3 mb-0">UNICODE POINT</h3>
                  <div class="text-body text-md leading-none">
                    <span>{{ curKanji.kanji.charCodeAt(0) }}</span>
                  </div>
                </div>
              </div>

              <div v-if="isKanji(curKanji.kanji)" class="ml-5">
                <div class="mb-4">
                  <h3 class="kk-RecognitionPane-h3 mb-0">Keyword</h3>
                  <div
                    class="font-serif italic text-[#42413d] text-[34px] leading-none"
                  >
                    <span>{{ curKanji.keyword }}</span>
                  </div>
                </div>

                <div class="mb-4">
                  <h3 class="kk-RecognitionPane-h3 mb-0">Heisig Index</h3>
                  <div
                    class="font-serif text-[#42413d] text-[34px] leading-none"
                  >
                    <span>{{ curKanji.seq_nr }}</span>
                  </div>
                </div>

                <a :href="getStudyPageLink(curKanji.kanji)" class="block"
                  >Go to study page</a
                >
              </div>
            </div>

            <div v-if="!isHiragana(curKanji.kanji)">
              <h3 class="kk-RecognitionPane-h3 mb-2">Dictionary</h3>
              <div class="bg-[#fff] -mx-4"> DICT HERE </div>

              <!-- ------------------------------------------------------ -->
              <!-- DICT -->
              <!-- ------------------------------------------------------ -->
              <div ref="refLoadingMask" class="dict-panel">
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
                <template v-else class="dict-list_info">
                  <p>No results.</p>
                </template>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { kk_globals_get } from "@app/root-bundle";
import { urlForStudy } from "@/lib/koohii";
import CjkLangJa from "@/vue/CjkLangJa.vue";
import * as wanakana from "wanakana";

import { DictId, DictListEntry } from "@app/api/models";

const DEFAULT_TEXT = `むかし、むかし、ご存知のとおり、うさぎとかめは、山の上まで競争しました。誰もが、うさぎの方がかめよりも早くそこに着くと思いました。しかし迂闊にも、うさぎは途中で寝てしまいました。目が覚めた時は、もうあとのまつりでした。かめはすでに山のてっ辺に立っていました。`;

type TRecKanji = {
  kanji: string; // single kanji
  seq_nr: TUcsId; // heisig index nr
  keyword: string;
  url?: string;
};

// --------------------------------------------------------------------
// hydration
// --------------------------------------------------------------------
type TUcsId = number;
type TKeywordMap = Map<TUcsId, string>; // UCS code, keyword
type TRtkIndexMap = Map<number, number>; // UCS code, Heisig Index

const keywordsMap = new Map(kk_globals_get("USER_KEYWORDS_MAP")) as TKeywordMap;
const getKeywordForUCS = (ucsId: TUcsId) => keywordsMap.get(ucsId) || "";

const knownKanji = kk_globals_get("USER_KNOWN_KANJI") as string;
const isKnownKanji = (char: string) => knownKanji.indexOf(char) >= 0;

const rtkIndexMap = new Map(kk_globals_get("RTK_INDEX_MAP")) as TRtkIndexMap;
const getIndexForUCS = (ucsId: TUcsId) => rtkIndexMap.get(ucsId) || 0;
// --------------------------------------------------------------------

export default defineComponent({
  name: "RecognitionApp",

  components: {
    CjkLangJa,
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
    this.onClickShow();
    this.curKanjiIndex = 10;
    this.curKanji = this.jtextarray[this.curKanjiIndex];
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
        let data: TRecKanji;
        // console.log(strKanji, kanjiInfo);

        if (isKnownKanji(char)) {
          data = {
            kanji: char,
            seq_nr: getIndexForUCS(ucsId),
            keyword: getKeywordForUCS(ucsId),
            url: `study/kanji/${ucsId}`,
          };
        } else {
          data = {
            kanji: char,
            seq_nr: 0,
            keyword: "",
          };
        }

        out.push(data);
      }

      this.jtextarray = out;
    },
  },
});
</script>

<style lang="scss"></style>
