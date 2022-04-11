<template>
  <div class="ko-LessonMap">
    <div v-for="(item, i) in values" :key="i">
      x
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from "vue";

type TItem = number;

export default defineComponent({
  name: "KoLessonMap",

  props: {
    values: { type: Array as PropType<TItem[]>, required: true },
  },

  computed: {},
});
</script>
