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
 *   Koohii.Util.Lang
 * 
 */

import Lang from './lang.js'
import Dom  from './dom.js'

export default {

  Dom,
  Util: {
    Lang
  }
  
}
