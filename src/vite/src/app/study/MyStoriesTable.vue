<template>
  <div class="flex items-center">
    <div class="mr-2">Sort</div>
    <div class="">
      <select v-model="selected" class="form-select w-[140px]">
        <option v-for="(option, i) in options" :key="i" :value="option.value">
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
// NOTE! converted from legacy js, the pager is still handled on php end

import { defineComponent } from "vue";
import { kk_globals_get } from "@app/root-bundle";
import AjaxTable from "@old/ajaxtable";
import AjaxPanel from "@old/ajaxpanel";

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
      ajaxTable.oAjaxPanel.post({ sort: value });
    },
  },

  mounted() {
    ajaxTable = new AjaxTable("MyStoriesComponent");
  },
});
</script>
