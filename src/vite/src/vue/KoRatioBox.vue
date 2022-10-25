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
/** 
 * NOTE   currently the ratio is set via CSS or style as a CSS var, cf. _RatioBox.scss
 * 
 *     --ko-ratiobox-ratio: 3/4;
 */
import { defineComponent } from "vue";

export default defineComponent({
  name: "KoRatioBox",

  props: {
    inner: { type: String, required: false, default: '' },
  },

});
</script>
