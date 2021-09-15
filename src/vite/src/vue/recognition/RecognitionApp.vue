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
              class="w-full mb-2 p-2 min-h-[100px] border border-[#ddd] rounded-lg text-lg"
            ></textarea>
            <input
              type="submit"
              class="btn btn-success"
              value="Show"
              @click.prevent="isStateEdit = false"
            />
          </form>

          <div id="introduction" class="markdown mt-8">
            <h3>Purpose of this page</h3>

            <p>
              In <em>Remembering the Kanji</em>, the Japanese characters are
              studied and reviewed from the keyword to the kanji. In this
              sight-reading section, you can test your memory the other way
              round, all the while seeing the characters <em>in context</em>.
            </p>

            <p
              >With very basic grammar you can locate compound words made of two
              or more kanji. You may be able to guess the meaning of some words
              based on the meaning of the characters.
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

        <div v-if="!isStateEdit">
          <div class="mb-4 pb-4 border-b border-[#d4cdba]">
            <button class="btn btn-success" @click="onClickToEdit"
              >Enter more japanese text</button
            >
          </div>
          <p>
            Point at the colored kanji with the mouse or click/tap to reveal the
            keyword. To study the character, <strong><em>click</em></strong> (or
            tap) the kanji, and then click the "Study" link inside the tooltip.
          </p>

          <div class="kk-Recognition-output">
            <cjk-lang-ja :html="transformJapaneseText"></cjk-lang-ja>
          </div>
        </div>
        sqdqsdqs
      </div>
      <!-- ------------------------------------------------------ -->
      <!-- SIDE COL -->
      <!-- ------------------------------------------------------ -->
      <div class="w-[400px] ml-4">
        <div class="bg-[#e7e1d3]">
          <div class="w-[75px] h-[75px] bg-[#fff] rounded text-[55px]">
            {{ kanji }}
          </div>

          <h3>Heisig Index</h3>
          <div>{{ heisigIndex }}</div>

          <h3>Keyword</h3>
          ...

          <h3>Dictionary</h3>
        </div>
        <div class="bg-[#fff]"> DICT HERE </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { kk_globals_get } from "@app/root-bundle";
import CjkLangJa from "@/vue/CjkLangJa.vue";

const DEFAULT_TEXT = `むかし、むかし、ご存知のとおり、うさぎとかめは、山の上まで競争しました。誰もが、うさぎの方がかめよりも早くそこに着くと思いました。しかし迂闊にも、うさぎは途中で寝てしまいました。目が覚めた時は、もうあとのまつりでした。かめはすでに山のてっ辺に立っていました。`;

type TRecKanji = {
  kanji: string; // single kanji
  seq_nr: number; // heisig index nr
  keyword: string;
};

type TRecKanjiArray = {
  [ucs_id: string]: TRecKanji;
};

const READING_KEYWORDS: TRecKanjiArray = kk_globals_get("READING_KEYWORDS");

export default defineComponent({
  name: "RecognitionApp",

  components: {
    CjkLangJa,
  },

  data() {
    return {
      japaneseText: DEFAULT_TEXT,

      isStateEdit: true,

      kanji: "思",
      heisigIndex: 651,
    };
  },

  computed: {
    transformJapaneseText(): string {
      const kanjis = READING_KEYWORDS;

      let text: string = this.japaneseText;

      console.log("text ", text);

      for (let ucsId in kanjis) {
        const kanjiInfo = kanjis[ucsId];

        // convert to html entity
        // const uniKanji = `&#${ucsId};`

        const title = `${kanjiInfo.keyword} (#${kanjiInfo.seq_nr})`;
        const html = `<a href="study/kanji/${ucsId}" data-text="${title}">${kanjiInfo.kanji}</a>`;

        // console.log("replace all ", kanjiInfo.kanji);

        text = text.replaceAll(kanjiInfo.kanji, html);
      }

      return text;
    },
  },

  mounted() {
    this.focusInput();
  },

  methods: {
    focusInput() {
      (this.$refs.input as HTMLElement).focus();
    },

    onClickToEdit() {
      this.isStateEdit = true;
      this.$nextTick(() => {
        this.focusInput();
      });
    },
  },
});
</script>

<style lang="scss"></style>
