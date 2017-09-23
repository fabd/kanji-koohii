<template>

  <!-- child of #uiFcMain -->

  <div
    v-if="is_displayed"
    v-bind:style="{ height: this.cardHeight + 'px' }"
    class="uiFcCard uiFcAction"
    :class="{
      'is-reverse': reviewMode.fc_reverse,
      'uiFcState-0': fc_state === 0,
      'uiFcState-1': fc_state === 1
    }"
    data-action="flip">
 
    <template v-if="!reviewMode.freemode">
      <a id="uiFcMenu" href="#" title="Edit Flashcard" class="uiGUI uiFcAction"
       data-action="fcmenu" :data-uri="reviewMode.fc_edit_uri" :data-param="reviewMode.fc_edit_params"><i class="fa fa-bars"></i></a>
    </template>

    <div id="keyword" v-html="cardData.keyword"></div>

    <div id="kanjibig">
      <p>
        <cjk_lang_ja :html="cardData.kanji"></cjk_lang_ja>
      </p>
    </div>

    <div id="strokecount" title="Stroke count">
      <cjk_lang_ja html="&#30011;&#25968;" className="kanji"></cjk_lang_ja><span>{{ cardData.strokecount }}</span>
    </div>

    <div id="framenum" v-html="cardData.framenum"></div>
    
    <div id="uiFcYomi" v-if="reviewMode.fc_yomi">
      <div class="pad">

        <div class="yomi y_o" v-if="cardData.v_on">
          <div>
            <cjk_lang_ja className="vyc">{{ cardData.v_on.compound }}</cjk_lang_ja>
            <cjk_lang_ja className="vyr" :html="cardData.v_on.reading"></cjk_lang_ja>
          </div>
          <div class="vyg">{{ cardData.v_on.gloss }}</div>
        </div>
        
        <div class="yomi y_k" v-if="cardData.v_kun">
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

  name: 'KoohiiFlashcard',

  components: {
    cjk_lang_ja
  },

  data() {
    return {
      resizedCard: false,

      cardHeight: 'auto'
    }
  },

  props: {
    //
    cardData: Object,

    // freemode      Boolean
    // fc_reverse    Boolean
    // fc_yomi       Boolean
    reviewMode: Object,

    //
    fc_state: { type: Number, default: 0 },
    //
    is_displayed: { type: Boolean, default: false }
  },

  /*
  
    fc_yomi: Boolean,

    //
    fc_reverse: Boolean,

    //
    fc_edit_uri: { type: String,, default: '' },
    fc_edit_params: { type: Object, default: null },


    //
    */ 

  methods: {
    
    setState(iState)
    {
      Core.log('setState(%i)', iState);
      this.fc_state = iState;
    },

    getState()
    {
      return this.fc_state;
    },

    display(bDisplay)
    {
      Core.log('KoohiiFlashcard::display(%o)', bDisplay);

      // mobile view support
      if (window.innerWidth <= 700)
      {
        if (!this.resizedCard)
        {
          this.resizedCard = 0;
        }

        if (bDisplay)
        {
    
          var wh = window.innerHeight; //document.documentElement.
          if (parseInt(wh))
          {
            var cardh = wh - (43+37+67);
            if (/*App.Ui.resizedCard === 0 &&*/ cardh > 150)
            {
              this.resizedCard = cardh;
              
              this.cardHeight = cardh;
              Core.log("@@@ resized card to "+cardh);
            }
          }
        }
      }

      this.is_displayed = bDisplay;
    }
  },

  // life cycle events

  beforeDestroy() {
    Core.log('KoohiiFlashcard::beforeDestroy()');
  },

  beforeMount() {
    Core.log('KoohiiFlashcard::beforeMount(%o)', this.cardData);
  },

  created() {
    Core.log('KoohiiFlashcard::created(%o)', this.cardData);
  }
}
</script>

<style>
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
.with-yomi #kanjibig  { width:50%; }

#uiFcYomi        { position:absolute; left:50%; top:0; width:50%; }

#uiFcYomi .yomi  { }

  /* highlight the split reading */
/*#uiFcYomi .vyc span  { padding-bottom:2px; border-bottom:2px solid #f00; }*/
#uiFcYomi .vyr span  { padding-bottom:2px; border-bottom:2px solid #f00; }

#uiFcYomi .pad   { padding:62px 8px 8px; }

#uiFcYomi .y_o   { margin:0 0 20px }

#uiFcYomi .cj-k  { line-height:1em; }
#uiFcYomi .vyc   { font-size:22px; padding:5px 3px; display:inline-block; background:#eee; border-radius:3px;  }
#uiFcYomi .vyr   { font-size:18px; padding:7px 3px; display:inline-block; margin:0 0 0 1em; }

#uiFcYomi .vyg   { 
  font:italic 15px/1.1em Georgia, serif; color:#888; padding:8px 0 0; 
  /* FIX #81 */
  overflow-y:auto; max-height:6em;
}
</style>
