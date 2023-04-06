<template>
  <div class="ko-LessonPane ko-Box ko-DashBox">
    <!-- --- -->
    <!-- TOP -->
    <!-- --- -->
    <div class="flex items-center mb-4">
      <h3 class="ko-DashBox-title mb-0">
        Lesson {{ lessonId }}
        <span class="font-normal">of {{ sequenceName }}</span>
      </h3>

      <a v-if="allLessonsUrl" :href="allLessonsUrl" class="ml-4 leading-1"
        >View all lessons</a
      >

      <!-- <div class="text-warm text-lg ml-auto">{{ lessonPos }} / {{ lessonCount }} kanji</div> -->

      <div
        class="px-2 py-1 rounded bg-[#E1FFC2] text-lg text-success-dark font-bold leading-1 ml-auto"
        >{{ `${kanjiCount} / ${lessonCount} kanji` }}</div
      >
    </div>

    <!-- --- -->
    <!-- MID -->
    <!-- --- -->
    <div class="flex items-center">
      <ko-lesson-map :cards="cards" class="flex-1 mr-4" />

      <button
        class="ko-Btn is-ghost ko-Btn--large ko-Btn--primary"
        @click="isOpen = !isOpen"
      >
        <span class="mbl:hidden mr-2">Show Kanji</span>
        <i
          class="fa fa-chevron-down"
          :class="[isOpen ? 'ux-rotateIcon180' : '']"
        ></i>
      </button>
    </div>

    <!-- --- -->
    <!-- MID -->
    <!-- --- -->
    <div v-if="isOpen" class="mt-5 pt-4 border-t border-dash-line">
      <transition appear name="lesson-fadein">
        <div
          :class="{
            'ko-LessonPane--maxHeight': maxHeight,
          }"
        >
          <ko-kanji-grid :cards="cards" />
        </div>
      </transition>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import * as USER from "@/lib/user";

import KoKanjiGrid from "@/vue/KoKanjiGrid.vue";
import KoLessonMap from "@/vue/KoLessonMap.vue";

export default defineComponent({
  name: "KoLessonPane",

  components: {
    KoKanjiGrid,
    KoLessonMap,
  },

  props: {
    lessonFrom: { type: Number, required: true },
    lessonId: { type: Number, required: true },
    lessonCount: { type: Number, required: true },
    sequenceName: { type: String, required: true },

    // this is set only on the home page Dashboard for the current lesson
    maxHeight: { type: Boolean, required: false, default: false },
    allLessonsCount: { type: Number, required: false, default: 0 },
    allLessonsUrl: { type: String, required: false, default: "" },
  },

  data() {
    return {
      isOpen: false,
      cards: [] as TKanjiCardData[],
    };
  },

  computed: {
    kanjiCount(): number {
      // count user's flashcards in this range (leitnerbox is not zero)
      return this.cards.reduce((count, { box }) => count + (box ? 1 : 0), 0);
    },

    // pctValue(): number {
    //   let pct = (this.lessonPos * 100) / this.lessonCount;
    //   let floor = Math.floor(pct);

    //   return pct > 0 ? Math.max(floor, 1) : 0;
    // },
  },

  beforeMount() {
    this.cards = USER.getKanjiCardDataForRange(
      this.lessonFrom,
      this.lessonFrom + this.lessonCount - 1
    );
  },
});
</script>
