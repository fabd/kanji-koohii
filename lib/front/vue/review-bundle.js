// bundle used on the Flashcard Review page mainly
// 
// requires the root bundle!

// Koohii
import KoohiiDictList  from 'components/KoohiiDictList.js'
import KoohiiFlashcard from 'components/KoohiiFlashcard.vue'

// TODO
// 
//   To be able to use new syntax and piece-meal upgrade old code:
//    
//   - import all the legacy code from kanji-flashcardreview.juicy.js
//   - refactor all the Juicer =require directives to modern JS import's


if (window.Koohii && window.Koohii.UX) {

  // we're not using Babel polyfill for Object.assign()
  let UX = window.Koohii.UX

  UX.KoohiiDictList  = KoohiiDictList    // dictionary list (Study & Flashcard Review)
  UX.KoohiiFlashcard = KoohiiFlashcard   // (wip) review page refactoring

}
else {
  console.warn('Koohii.UX not defined.')
}