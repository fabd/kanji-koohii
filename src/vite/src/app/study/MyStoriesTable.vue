<template>
  <div class="flex items-center">
    <div class="mr-2">Sort</div>
    <div class="">
      <select v-model="selected" class="form-control w-[140px]">
        <option v-for="option in options" v-bind:value="option.value">
          {{ option.text }}
        </option>
      </select>
    </div>
  </div>

  <div v-if="selected === 'public'" class="confirmwhatwasdone">
    Note: displaying only <strong>public stories</strong>.
  </div>
</template>

<script lang="ts">
// FIXME : converted from legacy js, make a "My Stories" component including table, pager, etc.

import { defineComponent } from "vue";
import { kk_globals_get } from "@app/root-bundle";
import AjaxTable from "@old/ajaxtable";

let ajaxTable: AjaxTable;

export default defineComponent({
  name: "MyStoriesTable",

  data() {
    return {
      selected: kk_globals_get("MYSTORIES_SORT_ACTIVE") as string,
      options: kk_globals_get("MYSTORIES_SORT_OPTIONS") as Dictionary,
    };
  },

  watch: {
    selected(value: string): void {
      let option = this.getOption(value);
      let oAjaxPanel = this.getAjaxPanel();
      (oAjaxPanel as any).post({ sort: option.value });
    },
  },

  methods: {
    getAjaxPanel() {
      if (!ajaxTable) {
        ajaxTable = new AjaxTable("MyStoriesComponent");
      }
      return ajaxTable.oAjaxPanel;
    },

    getOption(value: string) {
      for (let i = 0; i < this.options.length; i++) {
        if (this.options[i].value === value) {
          return this.options[i];
        }
      }
      return null;
    },
  },
});
</script>
