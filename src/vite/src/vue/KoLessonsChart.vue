<template>
  <div v-once class="ko-LessonsChart">
    <ko-lesson-pane
      v-for="(lessonData, index) in lessons"
      v-bind="getPropsForLesson(lessonData)"
      :key="index"
      class="mb-3"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";
import { TKanjiCardData } from "./KoKanjiCard.vue";
import KoLessonPane from "@/vue/KoLessonPane.vue";

type TLessonsChartLesson = {
  num: number; // lesson number, starts at 1
  from: number; // sequence index start of lesson, starts at 1
  pos: number; // kanji nr within lesson, starts at 1 (eg. "1 of 15")
  count: number;
}

export default defineComponent({
  name: "KoLessonsChart",

  components: {
    KoLessonPane,
  },

  props: {
    cards: { type: Array as PropType<TKanjiCardData[]>, required: true },
    lessons: { type: Array as PropType<TLessonsChartLesson[]>, required: true },
    sequenceName: { type: String, required: true },
  },

  methods: {
    getPropsForLesson(lesson: TLessonsChartLesson): any {
      const cardsForThisLesson = this.cards.slice(
        lesson.from - 1,
        lesson.from + lesson.count - 1
      );

      return {
        'cards': cardsForThisLesson,
        'lessonNum': lesson.num,
        'lessonPos': lesson.pos,
        'lessonCount': lesson.count,
        'sequenceName': this.sequenceName
      }
    },

  },
});
</script>
