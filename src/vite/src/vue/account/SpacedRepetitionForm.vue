<template>
  <div>
    <div class="form-group mb-8">
      <label for="srs_max_box" class="form-label block text-md"
        >Flashcard Options</label
      >
      <span class="form-text"
        >Choose whether to show <b>keyword</b> or <b>kanji</b> on the front of
        the flashcards. The default (keyword to kanji) follows James Heisig's
        recommendation (see Lesson 11 introduction in RTK Volume 1).</span
      >

      <div class="mt-2 mb-4">
        <div class="flex items-center mb-4">
          <em class="inline-block mr-8">Prompt:</em>

          <label class="flex items-center">
            <input
              v-model="srs_reverse"
              name="opt_srs_reverse"
              value="0"
              type="radio"
              class="mr-2"
              @change="animateCard"
            />
            <span>Keyword to kanji (default)</span>
          </label>
          <label class="flex items-center ml-8">
            <input
              v-model="srs_reverse"
              name="opt_srs_reverse"
              value="1"
              type="radio"
              class="mr-2"
              @change="animateCard"
            />
            <span>Kanji to keyword</span>
          </label>
        </div>

        <div class="bg-shaded rounded-lg p-4">
          <transition name="lesson-fadein">
            <div v-if="showPreview" class="flex items-center justify-center">
              <template v-for="front in [1, 0]" :key="front">
                <ko-ratio-box class="w-[140px]">
                  <div class="ko-FlashcardBg p-2 flex flex-col h-full">
                    <div>
                      <span v-if="front && isSrsReverse" class="text-body-light"
                        >&middot; &middot; &middot;</span
                      >
                      <span v-else class="text-link">apricot</span>
                    </div>

                    <div class="text-center my-auto text-[80px]"
                      ><span v-if="!front || isSrsReverse" title="杏"
                        >&#26447;</span
                      >
                      <span v-else class="my-auto">&nbsp;</span>
                    </div>

                    <div class="text-right">
                      <span v-if="!front">203</span>
                      <span v-else class="text-body-light"
                        >&middot; &middot; &middot;</span
                      >
                    </div>
                  </div>
                </ko-ratio-box>

                <span v-if="front" class="mx-8 text-[86px] text-[#DCD7CB]"
                  ><i class="fa fa-chevron-right"></i
                ></span>
              </template>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <div class="form-group mb-8">
      <label for="srs_max_box" class="form-label block text-md"
        >Number of boxes</label
      >
      <span class="form-text">
        How many boxes in total,
        <em>excluding</em> the leftmost box which contains New and Failed cards.
      </span>

      <select
        id="srs_max_box"
        v-model="srs_max_box"
        name="opt_srs_max_box"
        class="form-select max-w-[10em]"
      >
        <option v-for="(o, i) in srs_max_box_values" :key="i" :value="o[0]">
          {{ o[1] }}
        </option>
      </select>
    </div>

    <div class="form-group mb-8">
      <label for="srs_mult" class="form-label text-md"
        >Review interval multiplier</label
      >
      <span class="form-text">
        The multiplier determines the spacing between each successive review.
        The first interval is always 3 days.
      </span>

      <div class="mt-2 mb-4">
        <div class="flex items-center">
          <em class="inline-block mr-4">Intervals (days):</em>
          <span
            v-for="(i, k) in intervals"
            :key="k"
            class="mr-2 pt-2 pb-1 px-2 leading-1 font-mono bg-[#d7e0b5] text-[#485f27] border-b border-[#aab38a] rounded-md"
            >{{ i.days }}</span
          >
        </div>
      </div>

      <select
        id="srs_mult"
        v-model="srs_mult"
        name="opt_srs_mult"
        class="form-select max-w-[10em]"
      >
        <option v-for="(o, i) in srs_mult_values" :key="i" :value="o[0]">{{
          o[1] || o[0]
        }}</option>
      </select>
    </div>

    <div class="form-group mb-8" :class="{ 'has-error': !isValidHardBox }">
      <label for="srs_hard_box" class="form-label text-md"
        >Maximum box for cards marked 'Hard'</label
      >
      <span class="form-text">
        Here, you can chose the maximum interval for a Hard answer by limiting
        the upper box. So for example if you chose to use 10 boxes and a Hard
        answer limit of 5 then a card in box 6,7,8,9 and 10 will always drop
        down to 5.
      </span>

      <div class="mt-2 mb-4">
        <em class="mr-4">Max interval for Hard answer:</em>
        <span>
          {{
            srs_hard_box > 0
              ? nthInterval(srs_hard_box) + " days"
              : "(default : drop card by one box, use the lower box interval)"
          }}
        </span>
      </div>

      <select
        id="srs_hard_box"
        v-model="srs_hard_box"
        name="opt_srs_hard_box"
        class="form-select max-w-[10em]"
        :class="{
          'is-invalid': !isValidHardBox,
        }"
      >
        <option
          v-for="(o, index) in srs_hard_box_values"
          :key="index"
          :value="o[0]"
          >{{ o[1] || o[0] }}</option
        >
      </select>

      <span v-if="!isValidHardBox" class="invalid-feedback">
        ^ Max Hard Box must be lower than the number of boxes total.
      </span>
    </div>
  </div>
</template>

<script>
// NOTE : the validation needs to match the backend (account/spacedrepetition)

import { defineComponent } from "vue";
import { kk_globals_get } from "@app/root-bundle";

import KoRatioBox from "@/vue/KoRatioBox.vue";

export default defineComponent({
  name: "SpacedRepetitionForm",

  components: {
    KoRatioBox,
  },

  data() {
    return {
      // form
      srs_reverse: "0", // radio button
      srs_max_box: 0,
      srs_mult: 0, // integer (205 means 2.05)
      srs_hard_box: 0,

      showPreview: true,

      // select options
      srs_max_box_values: [
        [5, "5"],
        [6, "6"],
        [7, "7 (default)"],
        [8, "8"],
        [9, "9"],
        [10, "10"],
      ],
      srs_hard_box_values: [
        [0, "(default)"],
        [1],
        [2],
        [3],
        [4],
        [5],
        [6],
        [7],
        [8],
        [9],
      ],
    };
  },

  computed: {
    isSrsReverse() {
      return this.srs_reverse === "1";
    },

    isValidHardBox() {
      return this.srs_hard_box < this.srs_max_box;
    },

    srs_mult_values() {
      let m = 130;
      let options = [];
      while (m <= 400) {
        let label = m === 205 ? "2.05 (default)" : Number(m / 100).toFixed(2);
        options.push([m, label]);
        m += 5;
      }
      return options;
    },

    intervals() {
      let values = [];

      for (let n = 1; n <= this.srs_max_box; n++) {
        let days = this.nthInterval(n);
        values.push({ days: days });
      }

      return values;
    },
  },

  created() {
    // FIXME use props
    const srsSettings = kk_globals_get("ACCOUNT_SRS");

    this.srs_reverse = srsSettings.reverse || "0";
    this.srs_max_box = srsSettings.max_box;
    this.srs_mult = srsSettings.mult;
    this.srs_hard_box = srsSettings.hard_box;
  },

  methods: {
    animateCard() {
      this.showPreview = false;
      this.$nextTick(() => {
        this.showPreview = true;
      });
    },

    nthInterval(n) {
      let first = 3;
      let mult = 1.0 * Number(this.srs_mult / 100).toFixed(2); // 205 => 2.05
      return Math.ceil(first * Math.pow(mult, n - 1));
    },
  },
});
</script>
