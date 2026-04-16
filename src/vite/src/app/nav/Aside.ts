import { nextTick } from "vue";
import VueInstance from "@/lib/helpers/vue-instance";
import AsideComponent from "@/app/nav/Aside.vue";
import KoohiiNavMenu from "@/app/nav/KoohiiNavMenu.vue";

let instance: TVueInstanceOf<typeof AsideComponent> | null = null;
let navMenu: TVueInstanceOf<typeof KoohiiNavMenu> | null = null;

export default {
  // options.navOptionsmenu (cf. apps/koohii/templates/layout.php)
  open(mobileNavData: Dictionary) {
    console.log("Aside open()");

    if (!instance) {
      // render off-document and append afterwards
      const { vm } = VueInstance(AsideComponent, "#aside-component");
      instance = vm;

      // render nav
      const { vm: navMenuVm } = VueInstance(
        KoohiiNavMenu,
        (instance.$refs as any).navContent,
        {
          menu: mobileNavData,
        }
      );
      navMenu = navMenuVm;
    }

    instance.show = true;

    nextTick(() => {
      navMenu!.initCollapsedItems();
    });
  },

  close() {
    console.log("Aside close()");
    instance!.show = false;
  },

  toggle() {
    console.assert(instance !== null, "Aside not instanced before calling toggle()");
    if (instance && !instance.show) {
      instance.show = true;
    } else {
      this.close();
    }
  },
};
