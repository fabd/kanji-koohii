<template>
  <div
    class="ko-KanjiCard"
    :class="[{ 'is-on': card.box }]"
  >
    <div class="ko-KanjiCard-idx">
      <span>{{ getIndex() }}</span>
    </div>

    <div class="ko-KanjiCard-kwd">
      <a :href="getStudyUrl()">{{ getKeyword() }}</a>
    </div>

    <div class="ko-KanjiCard-chr">
      <cjk-lang-ja>{{ String.fromCodePoint(card.ucsId) }}</cjk-lang-ja>
    </div>

    <div v-if="card.tag" class="ko-KanjiCard-tag" :class="card.tag.css">
      <span>{{ card.tag.text }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, type PropType } from "vue";
import * as RTK from "@/lib/rtk";
import CjkLangJa from "@/app/common/components/CjkLangJa.vue";

export default defineComponent({
  name: "KoKanjiCard",

  components: {
    CjkLangJa,
  },

  props: {
    card: { type: Object as PropType<TKanjiCardItem>, required: true },
  },

  methods: {
    getKeyword(): string {
      return RTK.getKeywordForUCS(this.card.ucsId) || "-";
    },

    getIndex(): number {
      return RTK.getIndexForUCS(this.card.ucsId);
    },

    getStudyUrl(): string {
      return "/study/kanji/" + String.fromCodePoint(this.card.ucsId);
    },
  },
});
</script>
