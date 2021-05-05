import { nextTick } from "vue";
import VueInstance from "@lib/helpers/vue-instance";
import AsideComponent from "./Aside.vue";
import KoohiiNavMenu from "@/components/KoohiiNavMenu.vue";
import { Dictionary } from "@/types";

let instance: any = null;
let navMenu: any = null;

export default {
  // options.navOptionsmenu (cf. apps/koohii/templates/layout.php)
  open(options: Dictionary<any> = {}) {
    console.log("Aside open()");

    if (!instance) {
      // merged with defaults in the component
      let data = { width: 280 };

      // render off-document and append afterwards
      instance = VueInstance(AsideComponent, "#aside-component", data);

      // render nav
      navMenu = VueInstance(KoohiiNavMenu, instance.$refs.navContent, {
        menu: options.navOptionsMenu,
      });
    }

    instance.show = true;

    nextTick(() => {
      navMenu!.initCollapsedItems();
    });
  },

  close() {
    console.log("Aside close()");
    instance.show = false;
  },

  toggle() {
    console.assert(instance, "Aside not instanced before calling toggle()");
    if (!instance.show) {
      instance.show = true;
    } else {
      this.close();
    }
  },
};
