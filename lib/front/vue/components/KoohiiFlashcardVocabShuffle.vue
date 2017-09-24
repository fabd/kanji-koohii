<template>

  <div class="fc-vshuffle"> 

    <cjk_lang_ja className="ta-c dispword" :html="cardData.dispword"></cjk_lang_ja>
    <cjk_lang_ja className="ta-c reading">{{ cardData.reading }}</cjk_lang_ja>
    <div class="ta-c glossary">{{ cardData.glossary }}</div>

  </div>

</template>

<script>
import cjk_lang_ja from './cjk_lang_ja.vue'

export default {

  name: 'KoohiiFlashcardVocabShuffle',

  components: {
    cjk_lang_ja
  },

  data() {
    return {
    }
  },

  computed: {
    reviewMode() {
      // console.log('get reviewMode()')
      return this.$parent.reviewMode;
    },

    cardData() {
      // console.log('get cardData()')
      return this.$parent.cardData;
    }
  },

  methods: {
    //     
  }
}
</script>

<style>

.fc-vshuffle .ta-c { text-align: center; }

.fc-vshuffle .dispword { display:block; font-size:80px; line-height:1em; padding:50px 0 40px; }
.fc-vshuffle .dispword a { color:#0065b2; text-decoration:none; }
.fc-vshuffle .dispword a:hover { background:#eee; color:#000; }
.fc-vshuffle .reading  { display:block; font-size:27px; color:#0064b2; }
.fc-vshuffle .glossary { font:18px/1.2em Arial, sans-serif; color:#666; padding:40px 1em 0; }

/* states */
.uiFcState-0 .fc-vshuffle .reading   { visibility:hidden; }
.uiFcState-0 .fc-vshuffle .glossary  { visibility:hidden; color:#fff; }

.uiFcState-1 .fc-vshuffle .reading   { visibility:visible; }
.uiFcState-1 .fc-vshuffle .glossary  { visibility:visible; }

/* MOBILE small screens */
@media screen and (max-width:700px) {

  /* reduce font size otherwise gloss overlaps buttons below flashcard */
  .fc-vshuffle .dispword { font-size:50px; padding-bottom:30px; }
  .fc-vshuffle .glossary { font-size:17px; padding-top:30px; }
}

</style>
