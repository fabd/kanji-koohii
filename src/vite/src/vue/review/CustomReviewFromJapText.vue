<template>
  <div class="ko-CustomReviewForm">
    <h3 class="ko-CustomReviewForm-title mb-4">Create a Review Deck from Japanese Text</h3>

    <p>Paste <strong>japanese text</strong> (or any selection of kanji) below :</p>

    <div class="form-group">
      <textarea v-model="japText" class="form-control mb-1" rows="5"></textarea>
      <div class="text-right text-warm">
        <strong class="text-body">{{ countRtkKanji }}</strong> unique RTK kanji in text
      </div>
    </div>

    <form :action="actionUrl" method="post">
      <input type="hidden" name="from_text" :value="kanjiForReview" />

      <div class="form-group mb-1 -mt-1">
        <label>
          <input type="checkbox" name="shuffle" value="1" checked />
          <span>Shuffle cards</span>
        </label>
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="reverse" value="1" />
          <span>Kanji to Keyword (reverse mode)</span>
        </label>
      </div>

      <button
        type="submit"
        class="ko-Btn ko-Btn--success ko-Btn--large"
        :class="{
          'is-disabled': !formIsValid,
        }"
        :disabled="!formIsValid"
        @click="onSubmit"
        >Start Review<i class="fa fa-arrow-right ml-2"></i
      ></button>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { filterRtkKanji } from "@/lib/rtk";

export default defineComponent({
  name: "CustomReviewFromJapText",

  props: {
    actionUrl: { type: String, required: true },
  },

  data() {
    return {
      japText: "一二三四五六七八九十",
      kanjiForReview: "",
    };
  },

  computed: {
    uniqueRtkKanji(): string[] {
      const uniq = (arr: any[]) => [...new Set(arr)];
      return uniq(filterRtkKanji(this.japText.split("")));
    },

    countRtkKanji(): number {
      return this.uniqueRtkKanji.length;
    },

    formIsValid(): boolean {
      return this.countRtkKanji > 0;
    },
  },

  methods: {
    onSubmit(): boolean {
      // updates the input in the <form>
      this.kanjiForReview = this.uniqueRtkKanji.join("");

      return true;
    },
  },
});
</script>
