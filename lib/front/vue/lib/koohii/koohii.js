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
 *   Koohii
 *   Koohii.Dom
 *   Koohii.Dom.classList
 *   Koohii.Util.Lang
 * 
 */

import Lang from './lang.js'
import Dom, { classList }  from './dom.js'

// make non default exports available in the console 
//  (we don't care for tree-shaking atm, as we use pretty much evetything, even in "prod" build)
Dom.classList = classList

export default {

  Dom,
  Util: {
    Lang
  }
  
}
