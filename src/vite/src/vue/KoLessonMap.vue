<template>
  <div class="ko-LessonMap" :class="{
    'is-dense': cards.length >= 75,
    'is-tight': cards.length >= 50 && cards.length < 75
  }">
    <div v-for="(card, i) in cards" :key="i" :class="getCss(card)"></div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import { TKanjiCardData } from "@/vue/KoKanjiCard.vue";
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
