<template>

<div class="fc-kanji with-yomi">

  <template v-if="!reviewMode.freemode">
    <a id="uiFcMenu" href="#" title="Edit Flashcard" class="uiGUI uiFcAction"
     data-action="fcmenu" :data-uri="reviewMode.fc_edit_uri" :data-param="reviewMode.fc_edit_params"><i class="fa fa-bars"></i></a>
  </template>

  <div class="d-keyword" v-html="cardData.keyword"></div>

  <div class="d-strokec" title="Stroke count">
    <cjk_lang_ja html="&#30011;&#25968;" className="kanji"></cjk_lang_ja><span>{{ cardData.strokecount }}</span>
  </div>

  <div class="d-framenr" v-html="cardData.framenum"></div>

  <!-- inner content -->
  <div class="uiFcInner">

    <div class="uiFcHalf d-kanji">
      <!-- do this for now, until we position everything dynamically --> 
      <div class="tb">
        <div class="td">
          <cjk_lang_ja :html="cardData.kanji"></cjk_lang_ja>
        </div>
      </div>
    </div>
    
    <div class="uiFcHalf d-yomi"><!-- v-if="reviewMode.fc_yomi" -->
      <div class="d-yomi_pad">

        <div v-for="$item in vocab" class="uiFcYomi" :key="$item.dictid" @click.stop="onVocabClick">
          <div>
            <cjk_lang_ja className="vyc">{{ $item.compound }}</cjk_lang_ja>
            <cjk_lang_ja className="vyr" :html="$item.reading"></cjk_lang_ja>
          </div>
          <div class="vyg">{{ $item.gloss }}</div>
        </div>

        <div v-if="!vocab.length" class="uiFcYomi uiFcYomi--empty">
          <div>
            &nbsp;
          </div>
        </div>

      </div>
    </div>

  </div>

</div>

</template>

<script>
import cjk_lang_ja from './cjk_lang_ja.vue'

export default {

  name: 'KoohiiFlashcardKanji',

  components: {
    cjk_lang_ja
  },

  data() {
    return {
      /**
       * compound , reading , gloss
       * 
       */
      vocab: []
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
    onVocabClick() {
      console.log('onVocabClick')

      
    },

    /**
     * [setVocab description]
     * @param {object} item  vocab entry (compound, reading, gloss)
     */
    setVocab(item) {
      console.log('setVocab(%o)', item)
      this.vocab = [item]
    }
  },

  beforeMount() {
    console.log('KoohiiFlashcardKanji::beforeMount()')

    this.vocab = [{
      compound: '欠点', reading: '<em>けっ</em>てん', gloss: 'faults; defect; weakness'
    }]

    console.log(this.vocab)
  }

}
</script>

<style>

/* Kanji flashcard layout */
.fc-kanji .d-keyword { font-size:1.5em; padding:4px 40px 0 8px; min-height:40px; }
.fc-kanji .d-keyword a { text-decoration:none; }
.fc-kanji .d-keyword a:hover { text-decoration:underline; }

.fc-kanji .d-kanji { text-align:center; font-size:150px; line-height:1em; }
.fc-kanji .d-kanji .cj-k { display:block; /* v align */padding:0 0 0.2em; }

.fc-kanji .d-strokec { position:absolute; left:8px; bottom:6px; font:12px Georgia, Times New Roman; color:#a0a0a0; }
.fc-kanji .d-strokec .kanji { font-size:20pt; }

.fc-kanji .d-framenr { font:12px Georgia, Times New Roman; position:absolute; bottom:7px; right:8px; color:#a0a0a0; }


/* states :: default review mode */
.uiFcState-0 .fc-kanji .d-keyword   { color:#000; }
.uiFcState-0 .fc-kanji .d-kanji     { visibility:hidden; color:#fff; }
.uiFcState-0 .fc-kanji .d-strokec   { visibility:hidden; }
.uiFcState-0 .fc-kanji .d-framenr   { visibility:hidden; }
.uiFcState-0 .fc-kanji .d-yomi      { display:none; } /* vocab */

.uiFcState-1 .fc-kanji .d-keyword   { color:#a0a0a0 }
.uiFcState-1 .fc-kanji .d-kanji     { visibility:visible; color:#000; }
.uiFcState-1 .fc-kanji .d-strokec   { visibility:visible; }
.uiFcState-1 .fc-kanji .d-framenr   { visibility:visible; }
.uiFcState-1 .fc-kanji .d-yomi      { display:block; } /* vocab */

/* states :: kanji to keyword (explicitly override the defaults above) */
.uiFcState-0.is-reverse .fc-kanji .d-keyword { visibility:hidden; }
.uiFcState-0.is-reverse .fc-kanji .d-kanji   { visibility:visible; color:#000; }
.uiFcState-0.is-reverse .fc-kanji .d-yomi    { display:block; }
 
 /* show kanji compounds on front side, show meanings on back side */
.uiFcState-0.is-reverse .vyg       { visibility:hidden; }
.uiFcState-1.is-reverse .vyg       { visibility:visible; }


/* Edit Flashcard menu icon */
#uiFcMenu {
  display:block; position:absolute;
  right:0; top:0; width:40px; height:38px;
  background:#f2f2f2; text-decoration:none; text-align:center;
  z-index:1; /* clickable on top of .pad */
}
#uiFcMenu .fa { font-size:18px; line-height:38px; color:#9b9b9b; }
#uiFcMenu:hover, #uiFcMenu.active { background:#e8e8e8; }

/* Onyomi */

.d-yomi { font-size:20px; /* FIX #81 (don't overlap buttons below) */overflow-y:auto; }

  /* highlight the split reading */
.d-yomi .vyr em { padding-bottom:2px; border-bottom:2px solid #f00; font-style:normal; }

.d-yomi_pad    { padding:8px; }

.d-yomi .cj-k  { line-height:1em; }
.d-yomi .vyc   { font-size:1.5em; padding:5px 3px; display:inline-block; /*background:#eee; border-radius:3px;*/ }
.d-yomi .vyr   { font-size:1.2em; padding:7px 3px; display:inline-block; margin:0 0 0 1em; }

.d-yomi .vyg   { 
  font-size:0.85em; line-height:1.1em;
  font-style:italic;
  font-family:sans-serif;
  color:#888; padding:8px 0 0; 
}

.uiFcYomi {  }
.uiFcYomi:not(:first-of-type) { margin-top:10px; }
.uiFcYomi:hover { background:#eee; }

.uiFcYomi--empty { border:1px dashed #ddd;border-radius:4px; min-height:3em; }
.uiFcYomi--empty:hover { background:#eee; }

/* LAYOUT */

.uiFcInner {
  /* exclude top and bottom area of the card */
  position:absolute; top:40px; bottom:40px;

  /* flex layout of the main card content */
  width:100%; display:flex; /* FIREFOX bug? flex-wrap:wrap; */

  /*border:1px solid #fdd;  */
}
.uiFcInner .uiFcHalf {
  flex:0 0 100%; 
  /*border:1px solid #bfb;*/
}

.d-kanji .tb { display:table; width:100%; height:100%; border:none; }
.d-kanji .td { display:table-cell; vertical-align:middle; }

/* inner layout */
@media screen and (min-width:701px) {
  
  /* adjust layout for yomi */
  .with-yomi .uiFcHalf { flex:0 0 50%; height:100%; align-self:center; }

}


/* ================================================================================= 
   MOBILE LAYOUT 

   This stuff is a nightmare. Eventually we need a parent "flashcard page" container
   Vue template, which will figure out the position and size of the KoohiiFlashcard
   (this), based on window dimensions (so no media queries).
   And then the flashcard contents dynamically positioned (js).

   Either everything is CSS positioned (headaches), or dynamically (js) but atm
   with refactoring both are used, which makes this a mess.

   It's very hard also to properly center and balance elements vertically with css
   alone. But... can't be done until the full page is a vue template.

   =================================================================================  */

@media screen and (max-width:700px) {

  .fc-kanji .d-keyword     { /*font-size:2em;*/ padding:4px 0 0 8px; }

  .d-yomi        { font-size:25px; }
  .d-yomi_pad    { padding:0.5em 0.5em 0; }
  .d-yomi .vyc   { }
  .d-yomi .vyr   { }
  .d-yomi .vyg   { }

  /* PORTRAIT : vertical flow */
  .uiFcInner { flex-direction:column; flex-wrap:nowrap; }
  .d-yomi    { overflow-y:auto; }

  .with-yomi .uiFcHalf { flex:0 0 50%; }
  .with-yomi .d-kanji { flex:0 0 40%; }
  .with-yomi .d-yomi  { flex:0 0 60%; }

}

/* medium phone */
@media screen and (max-width:500px) {
  
  .fc-kanji .d-kanji { font-size:150px; }

  .d-yomi { font-size:25px; }
}

/* iPhone 5s & old devices + portrait */
@media screen and (max-width:320px) {
  
  /*.uiFcCard { background:#fee; }*/

  .with-yomi .d-kanji { font-size:70px; }

  .d-yomi { font-size:20px; }
}

</style>
