<template>
  <div class="p-4 leading-tight">
    <p class="mb-4"><strong>No common words found</strong> using this kanji.</p>
    <ul class="fa-ul mb-0">
      <li class="mb-4">
        <span class="fa-li"><i class="fas fa-search"></i></span>
        <a
          :href="jishoSearchUrl"
          target="blank"
          class="no-underline hover:underline"
          >Search words using {{ kanji }} on <strong>jisho.org</strong></a
        >
      </li>
      <li class="mb-4">
        <span class="fa-li"><i class="fas fa-arrow-right"></i></span>
        <a
          href="/learnmore#dictionary-sources"
          target="blank"
          class="no-underline hover:underline"
          >Learn more about Kanji Koohii's builtin dictionary</a
        >
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "KoDictEmpty",

  props: {
    ucsId: { type: Number, required: true },
  },

  computed: {
    kanji(): string {
      return String.fromCodePoint(this.ucsId);
    },

    jishoSearchUrl(): string {
      return `https://jisho.org/search/${this.kanji}`;
    },
  },
});
</script>
