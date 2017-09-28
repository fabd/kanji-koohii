<template>

<div class="fc-kanji">

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
      <cjk_lang_ja :html="cardData.kanji"></cjk_lang_ja>
    </div>
    
    <div class="uiFcHalf d-yomi" v-if="reviewMode.fc_yomi">
      <div class="d-yomi_pad">

        <div class="y_o" v-if="cardData.v_on">
          <div>
            <cjk_lang_ja className="vyc">{{ cardData.v_on.compound }}</cjk_lang_ja>
            <cjk_lang_ja className="vyr" :html="cardData.v_on.reading"></cjk_lang_ja>
          </div>
          <div class="vyg">{{ cardData.v_on.gloss }}</div>
        </div>
        
        <div class="y_k" v-if="cardData.v_kun">
          <div>
            <cjk_lang_ja className="vyc">{{ cardData.v_kun.compound }}</cjk_lang_ja>
            <cjk_lang_ja className="vyr" :html="cardData.v_kun.reading"></cjk_lang_ja>
          </div>
          <div class="vyg">{{ cardData.v_kun.gloss }}</div>
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

/* Kanji flashcard layout */
.fc-kanji .d-keyword { font-size:1.5em; padding:4px 40px 0 8px; min-height:40px; }
.fc-kanji .d-keyword a { text-decoration:none; }
.fc-kanji .d-keyword a:hover { text-decoration:underline; }

.fc-kanji .d-kanji { text-align:center; }
.fc-kanji .d-kanji .cj-k { font-size:100pt; line-height:1em; display:block; }

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

.d-yomi   { font-size:20px; }

  /* highlight the split reading */
.d-yomi .vyr em { padding-bottom:2px; border-bottom:2px solid #f00; font-style:normal; }

.d-yomi_pad    { padding:62px 8px 8px; }

.d-yomi .y_o   { margin:0 0 20px }

.d-yomi .cj-k  { line-height:1em; }
.d-yomi .vyc   { font-size:1.5em; padding:5px 3px; display:inline-block; /*background:#eee; border-radius:3px;*/ }
.d-yomi .vyr   { font-size:1.2em; padding:7px 3px; display:inline-block; margin:0 0 0 1em; }

.d-yomi .vyg   { 
  font-size:0.85em; line-height:1.1em;
  font-style:italic;
  font-family:sans-serif;
  color:#888; padding:8px 0 0; 
  /* FIX #81 */
  overflow-y:auto; max-height:6em;
}

/* layout of inner content */

.uiFcInner { position:absolute; left:0px; top:50px; width:100%; height:70%; display:flex; flex-wrap:wrap; /*border:1px solid #eee;*/  }
.uiFcInner .uiFcHalf { flex:0 0 100%; }
.d-kanji   { align-self:center; }

/* inner layout */
@media screen and (min-width:701px) {
  
  /* adjust layout for yomi */
  .with-yomi .uiFcHalf { flex:0 0 50%; }

}


/* ================================================================================= */
/* MOBILE LAYOUT */
/* ================================================================================= */

@media screen and (max-width:700px) {

  /* Kanji flashcard layout */
  .fc-kanji .d-keyword     { /*font-size:2em;*/ padding:4px 0 0 8px; }

  .fc-kanji .d-kanji .cj-k { padding-bottom:20px; }

  .d-yomi        { font-size:25px; }
  .d-yomi_pad    { padding:0.5em 0.5em 0; }
  .d-yomi .vyc   { }
  .d-yomi .vyr   { }
  .d-yomi .vyg   { }

}

</style>
