<template>
  <div class="ko-KanjiCard">
    <div class="ko-KanjiCard-idx">{{ card.id }}</div>
    <div class="ko-KanjiCard-key">{{ 'keyword' }}</div>
    <div class="ko-KanjiCard-chr">
      <cjk-lang-ja>{{ String.fromCodePoint(card.id) }}</cjk-lang-ja>
    </div>
    <div class="ko-KanjiCard-tag">
      <span>{{ card.tag }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";

import CjkLangJa from "@/vue/CjkLangJa.vue";

export type TKanjiCardData = {
  id: TUcsId;
  tag: string;
};

export default defineComponent({
  name: "KoKanjiCard",

  components: {
    CjkLangJa,
  },

  props: {
    card: { type: Object as PropType<TKanjiCardData>, required: true },
  },

});
</script>
