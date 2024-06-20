<template>
  <div v-once class="ko-LessonsChart">
    <ko-lesson-pane
      v-for="(lesson, index) in lessonsArr"
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
    sequenceName: {
      type: String,
      required: true,
    },
    lessons: {
      type: Map as PropType<TSeqLessonMap>,
      required: true,
    },
  },

  data() {
    return {
      lessonsArr: [] as TLessonsChartLesson[],
    };
  },

  beforeMount() {
    let lessonsArr = [] as TLessonsChartLesson[];

    this.lessons.forEach((value: TSeqLessonData, key: TSeqLessonId) => {
      // FIXME - hardcoded for now, we skip RTK Volume 3 in Old/New Editions
      //      a proper fix in case someday we want more custom sequences
      //      would require more info to identify Vol1.lessons
      // (the issue here being that we don't want to display a huge lesson with 800
      //  kanji, and we don't have lesson data for Vol 3. - so by design the
      //  Lessons chart covers 99% use case which is most users going through Vol 1)
      if (key !== 57) {
        lessonsArr.push({ id: key, from: value[0], count: value[1] });
      }
    });
    this.lessonsArr = lessonsArr;
  },
});
</script>
