<template>
  <div v-once class="ko-LessonsChart">
    <ko-lesson-pane
      v-for="(lesson, index) in lessons"
      :key="index"
      :lesson-from="lesson.from"
      :lesson-id="lesson.id"
      :lesson-count="lesson.count"
      :sequence-name="sequenceName"
      class="mb-3"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import KoLessonPane from "@/vue/KoLessonPane.vue";
import * as USER from "@/lib/user";

type TLessonsChartLesson = {
  id: number; // lesson number, starts at 1
  from: number; // sequence index start of lesson, starts at 1
  count: number;
};

export default defineComponent({
  name: "KoLessonsChart",

  components: {
    KoLessonPane,
  },

  props: {
    lessons: { type: Array as PropType<TLessonsChartLesson[]>, required: true },
    sequenceName: { type: String, required: true },
  },
});
</script>
