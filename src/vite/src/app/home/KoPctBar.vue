<template>
  <div>
    <div
      class="ko-PctBar"
      :class="{
        'is-zero': pctValue === 0,
      }"
    >
      <transition name="chart-fade" appear>
        <div
          class="ko-PctBar-fill"
          :style="{
            'min-width': cssFillWidth,
          }"
        >
          {{ pctMain }}{{ pctFrac }}%
        </div>
      </transition>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "KoPctBar",

  props: {
    value: { type: Number, required: true },
    maxValue: { type: Number, required: true },
  },

  computed: {
    cssFillWidth(): string {
      return `${this.pctValue}%`;
    },

    pctMain(): string {
      return "" + Math.floor(this.pctValue);
    },

    pctFrac(): string {
      return ("" + this.pctValue).replace(/^\d+/, "");
    },

    // return percentage with one decimal, round up non-zero progress to 0.1
    pctValue(): number {
      const pct = (this.value * 1000) / this.maxValue;
      const min = pct > 0 ? Math.max(pct, 1) : 0;
      return Math.floor(min) / 10;
    },
  },
});
</script>
