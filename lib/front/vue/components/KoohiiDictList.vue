<template>

  <div v-if="items.length > 0" class="dict-panel">

    <dl class="dict-list">
      <template v-for="$item in items">

        <dt>
          <span class="c cj-k" lang="ja" xml:lang="ja" v-html="$item.compound"></span>
          <span class="r cj-k" lang="ja" xml:lang="ja">{{ $item.reading  }}</span>
        </dt>
        <dd>{{ $item.glossary }}</dd>

      </template>
    </dl>

  </div>

  <div v-else class="dict-list_info">
      <p>There are no common words using this character.</p>
    </div>
  </div>

</template>

<style>
/* Dictionary Lookup Component */

  /* try to improve layout of the AjaxDialog on FLashcard Review */
.dict-panel { /*max-width:400px;*/ }

.dict-list { margin:0; background:#fff; }
.dict-list .cj-k  { line-height:1em; }
.dict-list dt    { padding:1em 20px;  border-top:1px solid #eee; font-weight:normal; }
.dict-list dt .c { font-size:22px; padding:0; display:inline-block; color:#000; /*border-radius:3px;*/ }
.dict-list dt .r { font-size:16px; padding:0; display:inline-block; margin:0 0 0 1em; color:#888; }
.dict-list dd    { padding:0 20px 1em; font:14px/1.3em Arial, sans-serif; color:#444; }

.dict-list dt u  { color:#f00; text-decoration:none; }

/* message when no words are found */
.dict-list_info { padding:1em 20px 0; color:#838279; }

</style>

<script>
/**
 * The dictionary list as seen in Study pages, and dictionary lookup in flashcard reviews.
 *
 * In the future, this list may allow to toggle bookmarking any individual entry, creating
 * a list of vocabulary for the user.
 * 
 */

export default {
  name: 'KoohiiDictList',

  props: [ 'items' ],

  data() {
    return {
    }
  },

  created() {
    Core.log('KoohiiDictList::created(%o)', this.items);
  },

  beforeDestroy() {
    Core.log('KoohiiDictList::beforeDestroy()');
  }

}
</script>
