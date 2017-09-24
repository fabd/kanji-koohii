<template>

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
 
    <component v-bind:is="currentView">
      <!-- component changes when vm.currentView changes! -->
    </component>

  </div>

</template>

<script>
import KoohiiFlashcardKanji        from './KoohiiFlashcardKanji.vue'
import KoohiiFlashcardVocabShuffle from './KoohiiFlashcardVocabShuffle.vue'

export default {

  name: 'KoohiiFlashcard',

  components: {
    // reviewMode.fc_view => Vue template
    'kanji':        KoohiiFlashcardKanji,
    'vocabshuffle': KoohiiFlashcardVocabShuffle
  },

  data() {
    return {
      currentView: KoohiiFlashcardKanji,

      resizedCard: false,

      cardHeight: 'auto'
    }
  },

  props: {
    //
    cardData: Object,

    // _ReviewKanji.php
    reviewMode: Object,

    //
    fc_state: { type: Number, default: 0 },
    //
    is_displayed: { type: Boolean, default: false }
  },

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

    // handle flashcard layout & interactivity as a child component according to review mode
    this.currentView = this.reviewMode.fc_view;
  }
}
</script>

<style>


</style>
