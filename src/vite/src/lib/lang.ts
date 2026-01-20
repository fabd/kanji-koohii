export default {
  isArray: (o: any): o is any[] => Array.isArray(o),
  isBoolean: (o: any): o is boolean => typeof o === "boolean",
  isFunction: (f: unknown): f is AnyFunction => typeof f === "function",
  isNumber: (s: any): s is number => typeof s === "number",
  isNull: (o: any): o is null => o === null,
  isObject: (o: any): o is object => o !== null && typeof o === "object",
  isString: (s: any): s is string => typeof s === "string",
  isUndefined: (o: any): o is undefined => typeof o === "undefined",

  // DOM
  isNode: (el: any): el is Node => el instanceof Node,
  isWindow: (o: any): o is Window => o === window,
};
