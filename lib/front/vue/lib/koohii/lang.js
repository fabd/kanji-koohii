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

  isArray:      (o)  => Object.prototype.toString.apply(o) === '[object Array]',
  isBoolean:    (o)  => typeof o === 'boolean',
  isFunction:   (o)  => typeof o === 'function',
  isObject:     (o)  => typeof o === 'object',
  isString:     (o)  => typeof o === 'string',
  isUndefined:  (o)  => typeof o === 'undefined',
  isDefined:    (o, p) => (p in o),

  /**
   * Returns a new object containing all of the properties of
   * all the supplied objects. The properties from later objects
   * will overwrite those in earlier objects.
   * 
   * Properties of value _undefined_ are neither merged, nor added
   * to the target object.
   * 
   * @param {Object} target      Object to merge properties into
   * @param {...Object} source   One or more objects supplying properties
   * 
   * @return {Object}  Returns the target object
   */
  merge(target) {
    console.assert(!!target, "merge() : invalid target.")
    for (let i = 1, j = arguments.length; i < j; i++) {
      let source = arguments[i] || {};
      for (let p in source) {
        if (source.hasOwnProperty(p)) {
          let value = source[p];
          if (value !== undefined) {
            target[p] = value;
          }
        }
      }
    }
    return target
  }
}

export default Lang