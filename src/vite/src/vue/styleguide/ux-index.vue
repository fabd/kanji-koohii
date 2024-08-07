<template>
  <div class="">
    <nav class="mb-4">
      &laquo; <a :href="baseUrl" class="mr-8">Back to Koohii</a>
      <a href="#/">Index</a> |
      <a href="#/buttons">Buttons</a> |
      <a href="#/fonts">Fonts</a> |
      <a href="#/ko-box">KoBox</a> |
      <a href="#/ko-pct-bar">KoPctBar</a>
    </nav>

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
import UxNotFound from "./ux-not-found.vue";

const routes: Record<string, any> = {
  "/": UxHome,
  "/buttons": UxButtons,
  "/fonts": UxFonts,
  "/ko-box": UxKoBox,
  "/ko-pct-bar": UxKoPctBar,
};

export default defineComponent({
  data() {
    return {
      baseUrl: kk_globals_get("BASE_URL"),
      currentPath: "/",
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
