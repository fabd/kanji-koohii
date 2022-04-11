<template>
  <div class="ko-Box ko-DashBox">
    <div class="flex items-center">
      <h3 class="ko-DashBox-title mb-0">
        Lesson {{ lessonNum }}
        <span class="font-normal">of {{ sequenceName }}</span>
      </h3>

      <a :href="allLessonsUrl" class="ml-4">Show all {{ allLessonsCount }} lessons</a>

      <!-- <div class="text-warm text-lg ml-auto">{{ lessonPos }} / {{ lessonCount }} kanji</div> -->

      <div class="px-2 py-1 rounded bg-[#E1FFC2] text-lg text-success-dark font-bold leading-1 ml-auto">
        {{
          // pctValue < 100 ? `${pctValue}% complete` : 'Completed!'
          `${lessonPos} / ${lessonCount} kanji`
        }}
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "KoLessonPane",

  props: {
    lessonNum: { type: Number, required: true },
    lessonPos: { type: Number, required: true },
    allLessonsCount: { type: Number, required: true },
    allLessonsUrl: { type: String, required: true },
    sequenceName: { type: String, required: true },
  },

  computed: {
    lessonCount(): number {
      return 15;
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
