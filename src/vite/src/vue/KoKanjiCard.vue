<template>
  <div class="ko-KanjiCard" :class="[{ 'is-box': card.box }, tagClass()]">
    <div class="ko-KanjiCard-idx">
      <span>{{ getIndex() }}</span>
    </div>
    <div class="ko-KanjiCard-kwd">{{ getKeyword() }}</div>
    <div class="ko-KanjiCard-chr">
      <cjk-lang-ja>{{ String.fromCodePoint(card.ucs) }}</cjk-lang-ja>
    </div>
    <div class="ko-KanjiCard-tag">
      <span>{{ getTagLabel() }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import * as RTK from "@/lib/rtk";
import CjkLangJa from "@/vue/CjkLangJa.vue";

export type TKanjiCardData = {
  ucs: TUcsId;
  box: number;
};

export default defineComponent({
  name: "KoKanjiCard",

  components: {
    CjkLangJa,
  },

  props: {
    card: { type: Object as PropType<TKanjiCardData>, required: true },
  },

  methods: {
    getKeyword(): string {
      return RTK.getKeywordForUCS(this.card.ucs) || "-";
    },

    getIndex(): number {
      return RTK.getIndexForUCS(this.card.ucs);
    },

    getTagLabel(): string {
      const box = this.card.box;
      const labels = ['NOT LEARNED', 'Restudy', 'Box 1', 'Box 2', 'Box 3', 'Box 4', 'Box 5', 'Box 6', 'Box 7', 'Box 8', 'Box 9', 'Box 10'];

      return labels[box] || '-';
    },

    tagClass(): string {
      return this.card.box ? `is-box-${this.card.box}`: '';
    }
  },
});
</script>
