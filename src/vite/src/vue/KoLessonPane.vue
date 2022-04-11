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

      <button class="ko-Btn is-ghost ko-Btn--large ko-Btn--primary">
        Show Kanji
        <i class="fa fa-chevron-down ml-2"></i>
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

import KoLessonMap from "@/vue/KoLessonMap.vue";

export default defineComponent({
  name: "KoLessonPane",

  components: {
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
