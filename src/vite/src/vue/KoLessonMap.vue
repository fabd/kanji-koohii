<template>
  <div
    class="ko-LessonMap"
    :class="{
      'is-sm': cards.length <= 30,
      'is-md': cards.length > 30 && cards.length <= 50,
      'is-lg': cards.length > 50,
    }"
  >
    <div class="ko-LessonMap-items">
      <div v-for="(card, i) in cards" :key="i" :class="getCss(card)"></div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, type PropType } from "vue";
import { getColorForCard } from "@/vue/KoKanjiCard.vue";

export default defineComponent({
  name: "KoLessonMap",

  props: {
    cards: { type: Array as PropType<TKanjiCardData[]>, required: true },
  },

  methods: {
    getCss(card: TKanjiCardData) {
      return getColorForCard(card);
    },
  },
});
</script>
