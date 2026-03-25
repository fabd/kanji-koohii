<template>
  <div class="p-[10px]" ref="maskArea">
    <template v-if="action === 'view-card'">
      <table class="ko-EditFlashcardDlg-stats">
        <tbody>
        <tr><th>Box</th><td><span v-html="statsBox"></span></td></tr>
        <tr><th>Passed</th><td><strong>{{ cardData.successcount }}</strong> time(s)</td></tr>
        <tr><th>Failed</th><td><strong>{{ cardData.failurecount }}</strong> time(s)</td></tr>
        <tr><th>Last review</th><td>{{ lastReview }}</td></tr>
        </tbody>
      </table>

      <div v-if="hasErrors()" class="text-red-500 mb-4">
        <span v-html="getErrors()"></span>
      </div>

      <div v-if="canDeleteCard()">
        <button class="ko-Btn ko-Btn--danger block w-full" @click.stop="action = 'delete'"><i class="fa fa-times mr-2"></i>Delete flashcard</button>
      </div>

      <div v-if="canFailCard()" class="mt-2">
        <button class="ko-Btn ko-Btn--success block w-full" @click.stop="onRestudy"><i class="fa fa-arrow-left mr-2"></i>Move card to restudy pile</button>
      </div>
    </template>

    <template v-if="action === 'delete'">
      <p class="text-md font-bold text-red-500 mb-2">Delete flashcard for {{ kanjiData.kanji }} (#{{ kanjiData.framenum }}) ?</p>
      <p class="italic">Note: only the flashcard is deleted, stories are not affected.</p>

      <div class="text-right">
      <button class="ko-Btn ko-Btn--danger mr-2" @click.stop="onDelete">Delete</button>
      <button class="ko-Btn is-ghost" @click.stop="action = 'view-card'">Cancel</button>
      </div>
    </template>

    <template v-if="action === 'delete-done'">
      <p class="text-md text-green-700 mb-2"><i class="fa fa-check mr-2"></i>Flashcard deleted.</p>
      <div class="text-center">
        <button class="ko-Btn ko-Btn--success block w-full JSDialogHide">Close</button>
      </div>
    </template>

    <template v-if="action === 'restudy-done'">
      <p class="text-md mb-2">Flashcard moved to restudy pile.</p>
      <div class="text-center">
        <button class="ko-Btn ko-Btn--success block w-full JSDialogHide">Close</button>
      </div>
    </template>

  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getApi } from "@app/api/api";
import { type ReviewData } from "@/app/api/models";
import { type KanjiData } from "@/app/api/models";
import KoohiiLoading from "@/vue/KoohiiLoading";
import eventBus from "@/lib/EventBus";
import { type TronInst } from "@/lib/tron";
import { type PostEditFlashcardResponse } from "@/app/api/models";

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
      // not review mode, not a card already in restudy pile
      return !this.reviewMode
        && this.cardData.leitnerbox > 1
        || this.cardData.totalreviews === 0;
    },

    canDeleteCard() {
      return !this.reviewMode;
    },

    onDelete() {
      KoohiiLoading.show({ target: this.$refs.maskArea as HTMLElement });

      getApi()
        .legacy.postEditFlashcard(this.kanjiData.ucs_id, "delete")
        .then((tron: TronInst<PostEditFlashcardResponse>) => {
          this.errors = tron.getErrors();
          if (tron.isSuccess()) {
            this.action = "delete-done"
            eventBus.notify("kk.flashcard.deleted");
          }
        })
        .finally(() => {
          KoohiiLoading.hide();
        });    
    },

    onRestudy() {
      KoohiiLoading.show({ target: this.$refs.maskArea as HTMLElement });

      getApi()
        .legacy.postEditFlashcard(this.kanjiData.ucs_id, "restudy")
        .then((tron: TronInst<PostEditFlashcardResponse>) => {
          this.errors = tron.getErrors();
          if (tron.isSuccess()) {
            this.action = "restudy-done"
            eventBus.notify("kk.flashcard.restudy");
          }
        })
        .finally(() => {
          KoohiiLoading.hide();
        });    
    }
  },
});
</script>
