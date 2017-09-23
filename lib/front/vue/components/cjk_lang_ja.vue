<template>
  <span class="cj-k" v-bind:class="className" lang="ja" xml:lang="ja">
    <span v-if="html" v-html="html"></span>
    <slot v-else></slot>
  </span>
</template>

<style>
</style>

<script>
/* 
  Refactored cjk_lang_ja() php helper.

  PROPERTIES

    className     (OPTIONAL) One or more css classnames
    html          (OPTIONAL) Use v-html instead of slot content


  USAGE

    <cjk_lang_ja className="css-class" html="<em>foo</em>"></cjk_lang_ja>

    or

    <cjk_lang_ja className="css-class">{{ content }}</cjk_lang_ja> 

*/

export default {

  name: 'cjk_lang_ja',

  props: {
    className: String,
    html: String
  }

}
</script>
