<template>
  <div class="ko-KanjiGrid">
    <template v-for="(cardData, i) in cards" :key="i">
      <ko-kanji-card :card="cardData" />
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent, type PropType } from "vue";

import { type TKanjiCardData } from "./KoKanjiCard.vue";

import KoKanjiCard from "@/vue/KoKanjiCard.vue";

export default defineComponent({
  name: "KoKanjiGrid",

  components: {
    KoKanjiCard,
  },

  props: {
    cards: { type: Array as PropType<TKanjiCardData[]>, required: true },
  },

});
</script>
