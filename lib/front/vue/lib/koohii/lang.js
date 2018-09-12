/**
 * Provides language utilities, used by the library.
 *
 *
 * NOTES
 *
 *   Creating a constant alias creates smaller code with babel:
 *
 *     const isString = Lang.isString
 *     if (isString(s)) { ... }
 * 
 */

let Lang = {

  isNode:       (el) => { return el instanceof window.Node; },
  isNodeList:   (el) => { return el instanceof window.NodeList || el instanceof NodeList || el instanceof HTMLCollection || el instanceof Array; },

  // misc helpers
  isBoolean:    (o)  => typeof o === 'boolean',
  isFunction:   (o)  => typeof o === 'function',
  isObject:     (o)  => typeof o === 'object',
  isString:     (o)  => typeof o === 'string',
  isUndefined:  (o)  => typeof o === 'undefined',
  isDefined:    (o, p) => (p in o)

}

export default Lang