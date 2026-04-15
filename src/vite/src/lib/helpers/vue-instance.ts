import {
  createApp,
  type Component,
} from "vue";
import Lang from "@/lib/lang";

export default <C extends Component>(
  component: C,
  mount: string | Element,
  props?: TVuePropsData,
  replace = false
): TVueInstanceRef<C> => {
  const el = Lang.isString(mount) ? document.querySelectorAll(mount)[0]! : mount;
  console.assert(Lang.isNode(el), "VueInstance() : mount is invalid");

  const app = createApp(component, props);

  // vm is TVueInstanceOf<typeof C>
  let vm: any;

  if (replace) {
    // NOTE! seems to work, but unsure if side effects
    const fragment = document.createDocumentFragment();
    vm = app.mount(fragment as Node as Element);
    el.parentNode!.replaceChild(fragment, el);
  } else {
    // appends as a child
    vm = app.mount(mount);
  }

  const unmount = () => {
    app.unmount();
  };

  return { vm, unmount };
};
