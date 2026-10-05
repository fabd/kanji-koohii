<template>
  <div class="fc-vshuffle">
    <cjk-lang-ja class="ta-c dispword" :html="cardData.dispword" />
    <cjk-lang-ja class="ta-c reading">{{ cardData.reading }}</cjk-lang-ja>
    <div class="ta-c glossary">{{ cardData.glossary }}</div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

import CjkLangJa from "@/app/common/components/CjkLangJa.vue";

export default defineComponent({
  name: "KoohiiFlashcardVocabShuffle",

  components: {
    CjkLangJa,
  },

  data() {
    return {};
  },

  computed: {
    // reviewMode() {
    //   // console.log('get reviewMode()')
    //   return this.$parent.reviewMode;
    // },

    cardData() {
      // console.log('get cardData()')
      return this.$parent.cardData;
    },
  },

  methods: {
    //
  },
});
</script>

<style>
/* =============================== */
/* refactor of Vocab Shuffle bundle, affects elements rom the php template, put here for now */
/* =============================== */

/* =============================== */
/* component styles */
/* =============================== */
.fc-vshuffle .ta-c {
  text-align: center;
}

.fc-vshuffle .dispword {
  display: block;
  font-size: 80px;
  line-height: 1em;
  padding: 50px 0 40px;
}
.fc-vshuffle .dispword a {
  color: var(--clr-0065b2);
  text-decoration: none;
}
.fc-vshuffle .dispword a:hover {
  background: var(--color-neutral-100);
  color: var(--color-black);
}
.fc-vshuffle .reading {
  display: block;
  font-size: 27px;
  color: var(--clr-0064b2);
}
.fc-vshuffle .glossary {
  font: 18px/1.2em Arial, sans-serif;
  color: var(--color-neutral-700);
  padding: 40px 1em 0;
}

/* states */
.uiFcState-0 .fc-vshuffle .reading {
  visibility: hidden;
}
.uiFcState-0 .fc-vshuffle .glossary {
  visibility: hidden;
  color: var(--color-white);
}

.uiFcState-1 .fc-vshuffle .reading {
  visibility: visible;
}
.uiFcState-1 .fc-vshuffle .glossary {
  visibility: visible;
}

/* MOBILE small screens */
@media screen and (max-width: 700px) {
  /* reduce font size otherwise gloss overlaps buttons below flashcard */
  .fc-vshuffle .dispword {
    font-size: 50px;
    padding-bottom: 30px;
  }
  .fc-vshuffle .glossary {
    font-size: 17px;
    padding-top: 30px;
  }
}
</style>
