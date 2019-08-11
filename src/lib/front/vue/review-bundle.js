/**
 * FLASHCARD REVIEW bundle
 */

import '@web/koohii/kanji-flashcardreview.build.scss';

// Koohii
import KoohiiDictList  from '@components/KoohiiDictList.vue'
import KoohiiEditStory from '@components/KoohiiEditStory.vue'
import KoohiiFlashcard from '@components/KoohiiFlashcard.vue'

// for legacy code upgrade path
import { KoohiiAPI }   from '@lib/KoohiiAPI.js'


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