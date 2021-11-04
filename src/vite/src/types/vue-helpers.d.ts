type ComponentPublicInstance = import("vue").ComponentPublicInstance;

// typing of props passed to createApps(root, props)
type TVuePropsData = Record<string, unknown>;

// extract component instance (component T's custom properties, methods, etc)
type TVueInstanceOf<T> = T extends new () => infer I ? I : never;

// return value of VueInstance() helper for manual mount/unmount
type TVueInstanceRef = { vm: any; unmount: () => void };
