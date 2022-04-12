<template>
  <span class="cj-k" lang="ja" xml:lang="ja">
    <span v-if="html" v-html="html"></span>
    <slot v-else></slot>
  </span>
</template>

<script lang="ts">
/*
  Equivalent of cjk_lang_ja() helper on the php side.

  PROPERTIES

    html          (OPTIONAL) Use v-html instead of slot content


  USAGE

    <cjk-lang-ja html="<em>七</em>"></cjk-lang-ja>

    or

    <cjk-lang-ja class="px-2 py-1">{{ content }}</cjk-lang-ja> 

*/
import { defineComponent } from "vue";

export default defineComponent({
  name: "CjkLangJa",

  props: {
    html: { type: String, default: "" },
  },
});
</script>
