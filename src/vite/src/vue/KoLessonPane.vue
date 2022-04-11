<template>
  <div class="ko-Box ko-DashBox">
    <!-- --- -->
    <!-- TOP -->
    <!-- --- -->
    <div class="flex items-center mb-4">
      <h3 class="ko-DashBox-title mb-0">
        Lesson {{ lessonNum }}
        <span class="font-normal">of {{ sequenceName }}</span>
      </h3>

      <a :href="allLessonsUrl" class="ml-4">Show all {{ allLessonsCount }} lessons</a>

      <!-- <div class="text-warm text-lg ml-auto">{{ lessonPos }} / {{ lessonCount }} kanji</div> -->

      <div
        class="px-2 py-1 rounded bg-[#E1FFC2] text-lg text-success-dark font-bold leading-1 ml-auto"
      >
        {{
          // pctValue < 100 ? `${pctValue}% complete` : 'Completed!'
          `${lessonPos} / ${lessonCount} kanji`
        }}
      </div>
    </div>

    <!-- --- -->
    <!-- MID -->
    <!-- --- -->
    <div class="flex items-center">
      <ko-lesson-map :values="lessonMap" class="flex-1 mr-4" />

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
      <ko-kanji-grid :cards="cards" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

import KoKanjiGrid from "@/vue/KoKanjiGrid.vue";
import KoLessonMap from "@/vue/KoLessonMap.vue";

export default defineComponent({
  name: "KoLessonPane",

  components: {
    KoKanjiGrid,
    KoLessonMap,
  },

  props: {
    cards: { type: Array, required: true },
    lessonNum: { type: Number, required: true },
    lessonPos: { type: Number, required: true },
    allLessonsCount: { type: Number, required: true },
    allLessonsUrl: { type: String, required: true },
    sequenceName: { type: String, required: true },
  },

  data() {
    return {
      isOpen: true,
    }
  },

  computed: {
    lessonCount(): number {
      return this.cards.length;
    },

    lessonMap(): number[] {
      let arr = this.cards.map(card => {
        return 1;
      });

      return arr;
    },

    pctValue(): number {
      let pct = (this.lessonPos * 100) / this.lessonCount;
      let floor = Math.floor(pct);

      return pct > 0 ? Math.max(floor, 1) : 0;
    },

    isSequenceComplete(): boolean {
      return this.lessonNum >= this.allLessonsCount;
    }

  },
});
</script>
