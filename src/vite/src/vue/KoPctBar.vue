<template>
  <div>
    <div class="ko-PctBar" :class="{
      'is-zero': pctValue === 0
    }">
      <transition name="chart-fade" appear>
        <div
          class="ko-PctBar-fill"
          :style="{
            'min-width': cssFillWidth
          }"
        >{{ pctValue }}%</div>
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

    pctValue(): number {
      let pct = (this.value * 100) / this.maxValue;
      let floor = Math.floor(pct);

      return pct > 0 ? Math.max(floor, 1) : 0;
    },
  },
});
</script>
