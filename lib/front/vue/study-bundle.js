// bundle used on the Study pages
// 
// requires the root bundle!

// Koohii
import LeitnerChart    from 'components/LeitnerChart.vue'
import KoohiiDictList  from 'components/KoohiiDictList.js'

if (window.Koohii && window.Koohii.UX) {

  // we're not using Babel polyfill for Object.assign()
  let UX = window.Koohii.UX

  UX.LeitnerChart    = LeitnerChart
  UX.KoohiiDictList  = KoohiiDictList    // dictionary list (Study & Flashcard Review)

}
else {
  console.warn('Koohii.UX not defined.')
}