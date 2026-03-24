<template>
  <div class="p-[10px]">
    <template v-if="action === 'view-card'">
      <table class="ko-EditFlashcardDlg-stats">
        <tr><th>Box</th><td><span v-html="statsBox"></span></td></tr>
        <tr><th>Passed</th><td><strong>{{ cardData.successcount }}</strong> time(s)</td></tr>
        <tr><th>Failed</th><td><strong>{{ cardData.failurecount }}</strong> time(s)</td></tr>
        <tr><th>Last review</th><td>{{ lastReview }}</td></tr>
      </table>
    </template>

    <div v-if="hasErrors()" class="text-red-500 mb-4">
      <span v-html="getErrors()"></span>
    </div>

    <template v-if="action == 'confirm-delete'">
      <p class="text-red-500">{{ message }}</p>
      <button class="ko-Btn ko-Btn--danger">Delete</button>
      <button class="ko-Btn is-ghost">Cancel</button>
    </template>

    <template v-if="action == 'show-message'">
      <p class="">{{ message }}</p>
      <button class="ko-Btn ko-Btn--success">Ok</button>
    </template>

    <template v-if="canFailCard()">
      <button class="ko-Btn ko-Btn--success">Move card to restudy pile</button>
    </template>

    <template v-if="canDeleteCard()">
      <button class="ko-Btn ko-Btn--success">Delete flashcard</button>
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getApi } from "@app/api/api";
import { type ReviewData } from "@/app/api/models";
import { type KanjiData } from "@/app/api/models";
import KoohiiLoading from "@/vue/KoohiiLoading";

export default defineComponent({
  name: "KoEditFlashcard",

  data() {
    return {
      action: "",
      message: "",
      errors: [] as string[],
    };
  },

  props: {
    kanjiData: { type: Object as () => KanjiData, required: true },
    cardData: { type: Object as () => ReviewData, required: true },
    reviewMode: { type: Boolean, default: false },
  },

  computed: {
    statsBox(): string {
      const card = this.cardData;
      let label: string;
      if (card.leitnerbox === 1) {
        label = card.totalreviews === 0
          ? '<span class="text-blue-500 font-semibold">New cards (blue pile)</span>'
          : '<span class="text-red-500 font-semibold">Restudy cards (red pile)</span>';
      }
      else {
        label = `${card.leitnerbox - 1}`;
      }

      return label;
    },

    lastReview() {
      // sql UNIX_TIMESTAMP is in seconds, we need milliseconds
      const ts = this.cardData.ts_lastreview * 1000;

      if (ts <= 0) {
        return "Not tested yet.";
      }

      return new Date(ts).toLocaleDateString("en-GB", { dateStyle: "medium" });
    },
  },

  created() {
    //
  },

  beforeUnmount() {
    //
  },

  mounted() {
    this.action = "view-card";
  },

  methods: {
    getErrors(): string {
      const errors = this.errors;
      return errors.length
        ? `<div class="mb-2">${errors.join("</div><div class=\"mb-2\">")}</div>`
        : "";
    },

    hasErrors() {
      return this.errors.length > 0;
    },

    canFailCard() {
      console.log("CANFAILLLLLL", this.reviewMode)
      return !this.reviewMode && this.cardData.leitnerbox > 1;
    },

    canDeleteCard() {
      return true;
    }
  },
});
</script>
