<template>

<div class="fc-kanji">

  <template v-if="!reviewMode.freemode">
    <a id="uiFcMenu" href="#" title="Edit Flashcard" class="uiGUI uiFcAction"
     data-action="fcmenu" :data-uri="reviewMode.fc_edit_uri" :data-param="reviewMode.fc_edit_params"><i class="fa fa-bars"></i></a>
  </template>

  <div class="d-keyword" v-html="cardData.keyword"></div>

  <div class="d-kanji">
    <p>
      <cjk_lang_ja :html="cardData.kanji"></cjk_lang_ja>
    </p>
  </div>

  <div class="d-strokec" title="Stroke count">
    <cjk_lang_ja html="&#30011;&#25968;" className="kanji"></cjk_lang_ja><span>{{ cardData.strokecount }}</span>
  </div>

  <div class="d-framenr" v-html="cardData.framenum"></div>
  
  <div class="d-yomi" v-if="reviewMode.fc_yomi">
    <div class="pad">

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
.fc-kanji .d-keyword { font:16px Georgia, Times New Roman, serif; position:absolute; left:8px; top:4px; width:204px; }
.fc-kanji .d-keyword a { text-decoration:none; }
.fc-kanji .d-keyword a:hover { text-decoration:underline; }

.fc-kanji .d-kanji { position:absolute; left:0px; width:100%; top:40%; margin-top:-50px; text-align:center; }
.fc-kanji .d-kanji p { margin:0; padding:0; font-size:100pt; line-height:1em; }
.fc-kanji .d-kanji p img { display:none; }

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
.with-yomi .d-kanji { width:50%; }

.d-yomi   { position:absolute; left:50%; top:0; width:50%; }

  /* highlight the split reading */
.d-yomi .vyr em { padding-bottom:2px; border-bottom:2px solid #f00; font-style:normal; }

.d-yomi .pad   { padding:62px 8px 8px; }

.d-yomi .y_o   { margin:0 0 20px }

.d-yomi .cj-k  { line-height:1em; }
.d-yomi .vyc   { font-size:22px; padding:5px 3px; display:inline-block; background:#eee; border-radius:3px;  }
.d-yomi .vyr   { font-size:18px; padding:7px 3px; display:inline-block; margin:0 0 0 1em; }

.d-yomi .vyg   { 
  font:italic 15px/1.1em Georgia, serif; color:#888; padding:8px 0 0; 
  /* FIX #81 */
  overflow-y:auto; max-height:6em;
}


/* ================================================================================= */
/* MOBILE LAYOUT */
/* ================================================================================= */

@media screen and (max-width:700px) {

  /* Kanji flashcard layout */
  .d-keyword     { padding:4px 0 0 8px; }
  .d-kanji       { top:40%; margin-top:-35px; }
  .d-kanji p     { font-size:100px; }

  .d-yomi .vyc   { font-size:16px; padding:2px 3px; }
  .d-yomi .vyr   { font-size:14px; padding:3px 3px; }
  .d-yomi .vyg   { font-size:13px; padding-top:0.5em; }

}

</style>
