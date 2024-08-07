<template>
  <div class="">
    <h2>KoPctBar</h2>

    <section class="ux-docSection ux-docDemoblock">
      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">0 %</span>
        <ko-pct-bar :value="0" :max-value="200" class="w-[100px]" />
      </div>
      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">0.x %</span>
        <ko-pct-bar :value="1" :max-value="200" class="w-[428px]" />
      </div>
      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">xx %</span>
        <ko-pct-bar :value="75" :max-value="200" class="w-[428px]" />
      </div>
      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">99.x %</span>
        <ko-pct-bar :value="199" :max-value="200" class="w-[428px]" />
      </div>
      <div class="mb-4 ux-flexButtonRow">
        <span class="self-center font-bold min-w-[120px]">100 %</span>
        <ko-pct-bar :value="200" :max-value="200" class="w-[428px]" />
      </div>
    </section>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

import KoPctBar from "@/vue/KoPctBar.vue";

export default defineComponent({
  components: {
    KoPctBar,
  },
});
</script>
