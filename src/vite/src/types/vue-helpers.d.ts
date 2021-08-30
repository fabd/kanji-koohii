type ComponentPublicInstance = import("vue").ComponentPublicInstance;

// typing of props passed to createApps(root, props)
type TVuePropsData = Record<string, unknown>;

// a generic Vue component instance
type TVueInstance = ComponentPublicInstance;

// extract component instance (component T's custom properties, methods, etc)
type TVueInstanceOf<T> = T extends new () => infer I ? I : never;
