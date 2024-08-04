<template>
  <ko-ratio-box
    class="ko-KanjiCard"
    :inner="'ko-KanjiCard-inner'"
    :class="[{ 'is-on': card.box }]"
  >
    <div class="ko-KanjiCard-idx">
      <span>{{ getIndex() }}</span>
    </div>

    <div class="ko-KanjiCard-kwd">
      <a :href="getStudyUrl()">{{ getKeyword() }}</a>
    </div>

    <div class="ko-KanjiCard-chr">
      <cjk-lang-ja>{{ String.fromCodePoint(card.ucsId) }}</cjk-lang-ja>
    </div>

    <div class="ko-KanjiCard-tag" :class="getTagCss()">
      <span>{{ getTagText() }}</span>
    </div>
  </ko-ratio-box>
</template>

<script lang="ts">
import { defineComponent, type PropType } from "vue";
import * as RTK from "@/lib/rtk";
import CjkLangJa from "@/vue/CjkLangJa.vue";
import KoRatioBox from "@/vue/KoRatioBox.vue";

export class SRS {
  static isCardNew = (card: TKanjiCardData) => card.isNew;
  static isCardRestudy = (card: TKanjiCardData) => card.box === 1;
}

export const getColorForCard = (card: TKanjiCardData) => {
  if (!card.box) {
    return "is-off";
  } else if (SRS.isCardNew(card)) {
    return "is-new";
  } else if (SRS.isCardRestudy(card)) {
    return "is-res";
  }

  const levels = ["", "", "is-L1", "is-L2", "is-L3", "is-L4"];

  return levels[card.box] || "is-L4";
};

export default defineComponent({
  name: "KoKanjiCard",

  components: {
    CjkLangJa,
    KoRatioBox,
  },

  props: {
    card: { type: Object as PropType<TKanjiCardData>, required: true },
  },

  methods: {
    getKeyword(): string {
      return RTK.getKeywordForUCS(this.card.ucsId) || "-";
    },

    getIndex(): number {
      return RTK.getIndexForUCS(this.card.ucsId);
    },

    getStudyUrl(): string {
      return "/study/kanji/" + String.fromCodePoint(this.card.ucsId);
    },

    getTagText(): string {
      const card = this.card;
      const labels = [
        "NOT LEARNED",
        "Restudy",
        "Box 1",
        "Box 2",
        "Box 3",
        "Box 4",
        "Box 5",
        "Box 6",
        "Box 7",
        "Box 8",
        "Box 9",
        "Box 10",
      ];

      if (SRS.isCardNew(card)) {
        return "New";
      }

      return labels[card.box] || "-";
    },

    getTagCss(): string {
      return getColorForCard(this.card);
    },
  },
});
</script>
