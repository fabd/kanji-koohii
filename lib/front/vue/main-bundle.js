// requires the root bundle!

// Koohii
import LeitnerChart    from 'components/LeitnerChart.vue'
import KoohiiDictList  from 'components/KoohiiDictList.js'
import KoohiiFlashcard from 'components/KoohiiFlashcard.vue'

if (window.Koohii && window.Koohii.UX) {

  // we're not using Babel polyfill for Object.assign()
  let UX = window.Koohii.UX

  UX.LeitnerChart    = LeitnerChart
  UX.KoohiiDictList  = KoohiiDictList    // dictionary list (Study & Flashcard Review)
  UX.KoohiiFlashcard = KoohiiFlashcard   // (wip) review page refactoring

}
else {
  console.warn('Koohii.UX not defined.')
}