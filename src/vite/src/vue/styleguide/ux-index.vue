<template>
  <div class="">
    <div class="flex">
      <div class="whitespace-nowrap flex-1">&laquo; <a :href="baseUrl" class="mr-8">Back to Koohii</a></div>
      <nav class="ux-DocNav-list mb-4">
        <a href="#/">Index</a>
        <a href="#/buttons">Buttons</a>
        <a href="#/fonts">Fonts</a>
        <a href="#/ko-box">KoBox</a>
        <a href="#/ko-pct-bar">KoPctBar</a>
        <a href="#/misc">Misc</a>
      </nav>
    </div>

    <component :is="currentView" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { kk_globals_get } from "@/app/root-bundle";

import UxHome from "./ux-home.vue";
import UxButtons from "./ux-buttons.vue";
import UxFonts from "./ux-fonts.vue";
import UxKoBox from "./ux-ko-box.vue";
import UxKoPctBar from "./ux-ko-pct-bar.vue";
import UxMisc from "./ux-misc.vue";
import UxNotFound from "./ux-not-found.vue";

const routes: Record<string, any> = {
  "/": UxHome,
  "/buttons": UxButtons,
  "/fonts": UxFonts,
  "/ko-box": UxKoBox,
  "/ko-pct-bar": UxKoPctBar,
  "/misc": UxMisc,
};

export default defineComponent({
  data() {
    return {
      baseUrl: kk_globals_get("BASE_URL"),
      currentPath: window.location.hash,
    };
  },
  computed: {
    currentView() {
      let hash = this.currentPath.slice(1) || "/";
      let view = routes[hash] || UxNotFound;
      console.log("currentView hash", hash);
      return view;
    },
  },
  mounted() {
    window.addEventListener("hashchange", () => {
      this.currentPath = window.location.hash;
    });
  },
});
</script>
