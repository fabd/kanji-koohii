<template>
  <div class="ko-CustomReviewForm">
    <h3 class="text-lg font-bold text-body mb-4">Create a Review Deck from Japanese Text</h3>

    <p>Paste japanese text (or any selection of kanji) below :</p>

    <form :action="actionUrl" method="post">
      <div class="form-group">
        <textarea v-model="japText" class="form-control mb-3" rows="5"></textarea>
      </div>

      <div class="form-group mb-1">
        <label>
          <input type="checkbox" name="shuffle" value="1" />
          <span>Shuffle cards</span>
        </label>
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" name="reverse" value="1" />
          <span>Kanji to Keyword (reverse mode)</span>
        </label>
      </div>

      <button type="submit" class="ko-Btn ko-Btn--success">Start Review<i class="fa fa-arrow-right ml-2"></i></button>
    </form>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "CustomReviewFromJapText",

  props: {
    actionUrl: { type: String, required: true },
  },

  data() {
    return {
      japText: "一二三四五六七八九十",
    };
  },

  methods: {},
});
</script>
