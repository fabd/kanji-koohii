/**
 * Create an object to be exported globally, containing our utilities.
 *
 *
 * USAGE
 *
 *   import obj from './lib/koohii/koohii.js'
 *   window.Koohii = obj
 *
 * 
 * STRUCTURE
 * 
 *   Koohii                  (common)
 *   Koohii.API              (study-bundle)
 *   Koohii.Dom              (common)
 *   Koohii.Util.Lang        (common)
 * 
 */

import Lang from './lang.js'
import Dom  from './dom.js'

// DEBUGGING ONLY
// import { classList, getNode }  from './dom.js'
// window.dev = { getNode }

export default {

  Dom,
  Util: {
    Lang
  }
  
}
