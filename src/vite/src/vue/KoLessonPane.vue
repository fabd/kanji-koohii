<template>
  <div class="ko-LessonPane ko-Box ko-DashBox">
    <!-- --- -->
    <!-- TOP -->
    <!-- --- -->
    <div class="flex items-center mb-4">
      <h3 class="ko-DashBox-title mb-0">
        Lesson {{ lessonNum }}
        <span class="font-normal">of {{ sequenceName }}</span>
      </h3>

      <a
        v-if="allLessonsUrl"
        :href="allLessonsUrl"
        class="ml-4"
      >View all {{ allLessonsCount }} lessons</a>

      <!-- <div class="text-warm text-lg ml-auto">{{ lessonPos }} / {{ lessonCount }} kanji</div> -->

      <div
        class="px-2 py-1 rounded bg-[#E1FFC2] text-lg text-success-dark font-bold leading-1 ml-auto"
      >{{ `${kanjiCount} / ${lessonCount} kanji` }}</div>
    </div>

    <!-- --- -->
    <!-- MID -->
    <!-- --- -->
    <div class="flex items-center">
      <ko-lesson-map :cards="cards" class="flex-1 mr-4" />

      <button class="ko-Btn is-ghost ko-Btn--large ko-Btn--primary" @click="isOpen = !isOpen">
        Show Kanji
        <i
          class="fa ml-2"
          :class="{
            'fa-chevron-down': isOpen,
            'fa-arrow-right': !isOpen,
          }"
        ></i>
      </button>
    </div>

    <!-- --- -->
    <!-- MID -->
    <!-- --- -->
    <div v-if="isOpen" class="mt-5 pt-4 border-t border-dash-line">
      <transition appear name="lesson-fadein">
        <div :class="{
          'ko-LessonPane--maxHeight': maxHeight
        }">
          <ko-kanji-grid :cards="cards" />
        </div>
      </transition>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";

import KoKanjiGrid from "@/vue/KoKanjiGrid.vue";
import KoLessonMap from "@/vue/KoLessonMap.vue";

import { TKanjiCardData } from "./KoKanjiCard.vue";

export default defineComponent({
  name: "KoLessonPane",

  components: {
    KoKanjiGrid,
    KoLessonMap,
  },

  props: {
    cards: { type: Array as PropType<TKanjiCardData[]>, required: true },
    lessonNum: { type: Number, required: true },
    lessonPos: { type: Number, required: true },
    allLessonsCount: { type: Number, required: false, default: 0 },
    allLessonsUrl: { type: String, required: false, default: '' },
    sequenceName: { type: String, required: true },
    maxHeight: { type: Boolean, required: false, default: false },
  },

  data() {
    return {
      isOpen: false,
    }
  },

  computed: {
    kanjiCount(): number {
      return this.cards.reduce((count, { box }) => { return count + (box ? 1 : 0); }, 0);
    },

    lessonCount(): number {
      return this.cards.length;
    },

    pctValue(): number {
      let pct = (this.lessonPos * 100) / this.lessonCount;
      let floor = Math.floor(pct);

      return pct > 0 ? Math.max(floor, 1) : 0;
    },
  },
});
</script>
