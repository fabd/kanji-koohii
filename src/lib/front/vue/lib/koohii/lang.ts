const Lang = {
  isArray: (o: any): boolean => Array.isArray(o),
  isBoolean: (o: any): o is boolean => typeof o === "boolean",
  isFunction: (f: any): f is Function => typeof f === "function",
  isNumber: (s: any): s is number => typeof s === "number",
  isObject: (o: any): o is object => typeof o === "object",
  isString: (s: any): s is string => typeof s === "string",
  isUndefined: (o: any): o is undefined => typeof o === "undefined",

  // DOM
  isNode: (el: any): boolean => el instanceof Node,
  isWindow: (o: any): o is Window => o === window,
};

/**
 * Merge all the properties of the supplied objects in `target`.
 *
 * The properties from later objects will overwrite those in earlier objects.
 *
 * NOTE!
 *   Unlike `Object.assign()` properties of value `undefined` are NOT
 *   merged in the target object.
 */
type MapLike = { [key: string]: any };
export function merge(target: MapLike, ...args: any[]): MapLike {
  console.assert(!!target, "merge() : invalid target.");

  for (let i = 0; i < args.length; i++) {
    let source = args[i];

    Object.keys(source).forEach((key) => {
      let sourceValue = source[key];
      if (sourceValue !== undefined) {
        target[key] = sourceValue;
      }
    });
  }

  return target;
}

export default Lang;
