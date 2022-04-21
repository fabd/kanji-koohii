<template>
  <div class="ko-RatioBox">
    <div class="ko-RatioBox-height">
      <div :class="['ko-RatioBox-inner', inner]">
        <slot />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "KoRatioBox",

  props: {
    inner: { type: String, required: false, default: '' },
  },

});
</script>
