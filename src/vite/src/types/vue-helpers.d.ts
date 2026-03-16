// typing of props passed to createApps(root, props)
type TVuePropsData = Record<string, unknown>;

/**
 * Extract component instance (component T's custom properties, methods, etc)
 *
 *   import MyComponent from "@/vue/MyComponent.vue";
 *   const instance: TVueInstanceOf<typeof MyComponent>;
 *   const { vm } = VueInstance(MyComponent, elMount);
 *   instance = vm;
 */
type TVueInstanceOf<T> = T extends new () => infer I ? I : never;

/**
 * Return value of VueInstance() helper for manual mount/unmount.
 * 
 *   const ref: TVueInstanceRef<typeof MyComponent>;
 *   ref = VueInstance(MyComponent, elMount);
 *   ref.vm.componentMethod(...);
 *   ref.unmount();
 */
type TVueInstanceRef<T> = {
  vm: TVueInstanceOf<T>;
  unmount: () => void;
};
