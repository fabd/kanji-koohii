import { createApp, DefineComponent, ComponentPublicInstance } from "vue";
import Lang from "@lib/core/lang";

export type { ComponentPublicInstance };

// type ExtractComponentInstance<T> = T extends new () => infer I ? I : never;

const fnVueInstance = (
  component: any,
  mount: string | Element,
  props?: TVuePropsData
): ComponentPublicInstance => {
  let el = Lang.isString(mount) ? document.querySelectorAll(mount)[0] : mount;
  console.assert(Lang.isNode(el), "VueInstance() : mount is invalid");

  const app = createApp(component, props);
  const vm = app.mount(mount);

  return vm;
};

export default fnVueInstance;
