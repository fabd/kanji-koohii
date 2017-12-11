// bundle used on the Flashcard Review page mainly
// 
// requires the root bundle!


// LEGACY CODE ! (experiment)
// 
//   To be able to use new syntax and piece-meal upgrade old code:
//    
//   - import all the legacy code from kanji-flashcardreview.juicy.js
//   - refactor all the Juicer =require directives to modern JS import's

import { AjaxQueue } from 'core_legacy/ui/ajaxqueue.js'
Core.Ui.AjaxQueue = AjaxQueue

import { Keyboard } from 'core_legacy/ui/keyboard.js'
Core.Ui.Keyboard = Keyboard

import { FlashcardReview } from 'web_revtk/classes/FlashcardReview.js'
App.Ui.FlashcardReview = FlashcardReview


// Koohii
import KoohiiDictList  from 'components/KoohiiDictList.js'
import KoohiiFlashcard from 'components/KoohiiFlashcard.vue'


if (window.Koohii && window.Koohii.UX) {

  // we're not using Babel polyfill for Object.assign()
  let UX = window.Koohii.UX

  UX.KoohiiDictList  = KoohiiDictList    // dictionary list (Study & Flashcard Review)
  UX.KoohiiFlashcard = KoohiiFlashcard   // (wip) review page refactoring

}
else {
  console.warn('Koohii.UX not defined.')
}