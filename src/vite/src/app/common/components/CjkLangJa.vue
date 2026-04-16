<template>
  <span class="cj-k" lang="ja">
    <span v-if="html" v-html="html"></span>
    <slot v-else></slot>
  </span>
</template>

<script lang="ts">
/** 
 * Helper which adds language attributes and applies a font stack via css class.
 * 
 * Equivalent of cjk_lang_ja() helper on the php side.
 * 
 * EXAMPLES
 *   <cjk-lang-ja class="px-2 py-1">{{ content }}</cjk-lang-ja> 
 *   <cjk-lang-ja :html="<em>七</em>"></cjk-lang-ja>
 * 
 * SOURCES
 *   https://www.w3.org/International/questions/qa-css-lang#which
 * 
 */
import { defineComponent } from "vue";

export default defineComponent({
  name: "CjkLangJa",

  props: {
    html: { type: String, default: "" },
  },
});
</script>
