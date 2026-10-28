<template>
  <div>
    <p class="mb-4">
      Select a color theme for the website. Tired eyes? Try Koohii Dark!
    </p>

    <ul class="list-none p-0 mb-6">
      <li v-for="(theme, id) in themes" :key="id" class="mb-3">
        <label class="flex items-center cursor-pointer">
          <input
            v-model="selected"
            type="radio"
            name="theme"
            :value="id"
            class="mr-3"
            @change="previewTheme"
          />
          <span
            class="inline-block w-6 h-6 rounded-md mr-3 border border-(--color-box-bg-dark)"
            :style="{ backgroundColor: theme.themeColor }"
          ></span>
          <span>{{ theme.label }}</span>
        </label>
      </li>
    </ul>

    <p v-if="errorMessage" class="text-(--color-danger) mb-4">{{ errorMessage }}</p>

    <div class="flex items-center">
      <button
        type="button"
        class="ko-Btn ko-Btn--success"
        :disabled="isSaving"
        @click="save"
        >Save changes</button
      >
      <span v-if="showSaved" class="ml-4 text-(--color-success)">Saved!</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { kk_globals_get } from "@/app/common/root-bundle";
import { getApi } from "@/app/api/api";
import type { PostThemeResponse } from "@/app/api/models";
import type { TronInst } from "@/lib/tron";

const SAVED_MESSAGE_MS = 2000;

export default defineComponent({
  name: "KoThemeForm",

  data() {
    const { themes, current } = kk_globals_get("ACCOUNT_THEMES");

    return {
      themes,
      selected: current,
      isSaving: false,
      showSaved: false,
      errorMessage: "",
      savedTimer: 0,
    };
  },

  methods: {
    // live preview, the theme is only persisted with the save button
    previewTheme() {
      document.documentElement.setAttribute("data-theme", this.selected);
    },

    save() {
      this.isSaving = true;
      this.errorMessage = "";

      getApi()
        .postTheme(this.selected)
        .then((tron: TronInst<PostThemeResponse>) => {
          this.isSaving = false;

          if (tron.hasErrors()) {
            this.errorMessage = tron.getErrors().join(" ");
            return;
          }

          this.showSaved = true;
          window.clearTimeout(this.savedTimer);
          this.savedTimer = window.setTimeout(() => {
            this.showSaved = false;
          }, SAVED_MESSAGE_MS);
        });
    },
  },
});
</script>
