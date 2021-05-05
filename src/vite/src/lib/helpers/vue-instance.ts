import { createApp, DefineComponent, ComponentPublicInstance } from "vue";
import Lang from "@lib/core/lang";
import { TVueDefine, TVuePropsData, TVueInstance } from "@/types/index";

export type { TVueDefine, TVuePropsData, TVueInstance };

const fnVueInstance = function (
  rootComponent: TVueDefine,
  mount: string | Element,
  rootProps: TVuePropsData
): TVueInstance {
  let el = Lang.isString(mount) ? document.querySelectorAll(mount)[0] : mount;
  console.assert(Lang.isNode(el), "VueInstance() : mount is invalid");

  const app = createApp(rootComponent, rootProps);
  const vm = app.mount(mount);

  return vm;
};

export default fnVueInstance;
