// bundle used on the Flashcard Review page mainly
// 
// requires the root bundle!


// LEGACY CODE ! (experiment)
// 
//   To be able to use new syntax and piece-meal upgrade old code:
//    
//   - import all the legacy code from kanji-flashcardreview.juicy.js
//   - refactor all the Juicer =require directives to modern JS import's

import { AjaxQueue }       from 'legacy/ajaxqueue.js'
Core.Ui.AjaxQueue      = AjaxQueue
import { Keyboard }        from 'legacy/keyboard.js'
Core.Ui.Keyboard       = Keyboard
import { FlashcardReview } from 'legacy/FlashcardReview.js'
App.Ui.FlashcardReview = FlashcardReview

// Koohii
import KoohiiDictList  from 'components/KoohiiDictList.vue'
import KoohiiEditStory from 'components/KoohiiEditStory.vue'
import KoohiiFlashcard from 'components/KoohiiFlashcard.vue'

// for legacy code upgrade path
import { KoohiiAPI }   from 'lib/KoohiiAPI.js'


if (window.Koohii && window.Koohii.UX) {

  // we're not using Babel polyfill for Object.assign()
  let UX = window.Koohii.UX

  UX.KoohiiDictList  = KoohiiDictList    // dictionary list (Study & Flashcard Review)
  UX.KoohiiFlashcard = KoohiiFlashcard   // (wip) review page refactoring
  UX.KoohiiEditStory = KoohiiEditStory   // Edit Story dialog

  // for legacy code upgrade path
  Koohii.API = KoohiiAPI
}
else {
  console.warn('Koohii.UX not defined.')
}