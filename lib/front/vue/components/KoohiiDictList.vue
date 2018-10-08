<template>
<div>

  <div v-if="isLoading">
    <div class="dict-panel" ref="maskArea">
      <div style="min-height:100px;"></div>
    </div>
  </div>

  <div v-if="!isLoading && items.length" class="dict-panel">

    <div class="dict-list">
      <template v-for="$item in items">

      <div :class="[ 'dl_item', { 'dl_item--pick': $item.pick }]"  :key="$item.dictid" @click="onVocabPick($item)">

        <div class="dl_t">

          <div class="dl_t_menu">
            <i v-if="$item.pick === true" class="fa fa-star"></i>
            <i v-else class="far fa-star"></i>
          </div>

          <cjk_lang_ja className="c" :html="$item.compound"
            :class="{ known: $item.known }"></cjk_lang_ja>
          <cjk_lang_ja className="r" :html="$item.reading"></cjk_lang_ja>
        </div>
        <div class="dl_d">
          {{ $item.glossary }}
        </div>

        <div if="isMenu">
          
        </div>
      
      </div>
      
      </template>
    </div>

  </div>

  <div v-if="!isLoading && items.length === 0" class="dict-list_info">
      <p>There are no common words using this character.</p>
    </div>
  </div>

</div>
</template>

<script>
/**
 * The dictionary list as seen in Study pages, and dictionary lookup in flashcard reviews.
 *
 * In the future, this list may allow to toggle bookmarking any individual entry, creating
 * a list of vocabulary for the user.
 * 
 */

import { KoohiiAPI, TRON } from 'lib/KoohiiAPI.js'

// comps
import cjk_lang_ja from './cjk_lang_ja.vue'

//mixins
import KoohiiLoading       from 'lib/mixins/KoohiiLoading.js'


// our simple regexp matching needs this so that vocab with okurigana is considered known
const HIRAGANA = 'ぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをんゔゕゖ ゙ ゚゛゜ゝゞゟ'
const KATAKANA = '゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ'
const PUNCTUATION = '｟｠｡｢｣､･ｦｧｨｩｪｫｬｭｮｯｰｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜﾝﾞ'

// cf. rtkLabs.php (this will go into an include)
/*
const PRI_ICHI1 = 0X80
const PRI_NEWS1 = 0X40
const PRI_NEWS2 = 0X20
const PRI_ICHI2 = 0X10
const PRI_SPEC1 = 8
const PRI_SPEC2 = 4
const PRI_GAI1  = 2
const PRI_GAI2  = 1
*/


export default {
  name: 'KoohiiDictList',

  components: {
    cjk_lang_ja
  },

  mixins: [
    KoohiiLoading
  ],

  props: {
    /**
     * 
     *   compound:   "描写"
     *   dictid:     "value", "1490140"
     *   glossary:   "depiction; description; portrayal"
     *   pri:        "192"
     *   reading:    "びょうしゃ"
     *
     *   pick:       {boolean}    user selected this item
     *
     *   known       {boolean}    contains only known kanji
     *
     */
    items: { type: Array, default: function() { return [] } }
  },

  data() {
    return {
      isLoading: true,

      // whether we have already requested them from server
      isSetKnownKanji: false,

      // a string containing all kanji known by the user
      knownKanji: ''
    }
  },

  methods: {

    onVocabPick(item)
    {
      console.log('onVocabPick "%s"', item.compound)

      this.items.forEach((o) => { o.pick = false })
      
      item.pick = true

      // update the flashcard view
      // App.KanjiReview.oReview.curCard.cardData.v_on ...   
      if (App.KanjiReview) {
        let vmFlashcard = App.KanjiReview.oReview.getFlashcard()
        let vmKanjiCard = vmFlashcard.getChild()
        console.log('child = %o', vmKanjiCard)
        vmKanjiCard.setVocab({
          compound: item.compound,
          reading:  item.reading,
          gloss:    item.glossary
        })
      }
      /*
        - ajouter ref="" dans kohiiflashcard

          getCard
       */


    },

    load(ucsId)
    {
      this.isLoading = true

      function doLoad() {
        this.koohiiloadingShow({ target: this.$refs.maskArea })

        // getKnownKanji:
        // 
        //   We request these only once for the lifetime of the component. This is more
        //   efficient in the flashcard review page.
        //   
        //   The user's known kanji could realistically be 2000 to 3000 utf8 characters. So
        //   even though they are also cached in php session, it's better to avoid returning
        //   several KBs of data with each dictionary lookup request

        KoohiiAPI.getDictListForUCS({
          ucsId: ucsId,
          getKnownKanji: false === this.isSetKnownKanji
        },
        {
          then: this.onDictListResponse.bind(this)
        });
      }

      this.$nextTick(doLoad)
    },

    onDictListResponse(tron)
    {
      const props = tron.getProps()

      // console.log('onDictListResponse(%o)', props)
// return
      this.koohiiloadingHide()

      if (props.known_kanji) {
        this.knownKanji = props.known_kanji
        this.isSetKnownKanji = true
      }

      let items = this.setKnownItems(props.items, this.knownKanji)

//test
// if (items.length > 1) { items[1].pick = true }
items.forEach((o) => { o.pick = false })

      this.items = items

      this.isLoading = false
    },

    setKnownItems(items, knownKanji)
    {
      // if (this.known_kanji !== '') {
      //   console.log(' known_kanji : ' + this.known_kanji)
      // }
      const KNOWN_KANJI = knownKanji + HIRAGANA + KATAKANA + PUNCTUATION

      // a basic string search could be faster - it's a very small list though
      const regexp = new RegExp('^['+KNOWN_KANJI+']+$')
      items.forEach(item => {
        item.known = regexp.test(item.compound)
      })

      // sort known vocab first
      let knownItems  = items.filter(o => o.known === true)
      let unkownItems = items.filter(o => o.known === false)
      let sortedItems = knownItems.concat(unkownItems)

      return sortedItems
    },

    sortSelectedItems(items)
    {
      let picks = items.filter(o => o.pick === true)
      let other = items.filter(o => o.pick !== true)  // undefined
      return picks.concat(other)
    }
  },

  created() {
    console.log('KoohiiDictList::created(%o)', this.items);

    this.isLoading = true
  },

  beforeDestroy() {
    console.log('KoohiiDictList::beforeDestroy()');
  }

}
</script>

<style>
/* Dictionary Lookup Component */

.dict-panel { background:#fff; max-height:80vh; overflow-y:auto; }

.dict-list { margin:0; background:#444; }

.dict-list .dl_item { background:#fff; border-bottom:1px solid #eee; position:relative; }
.dict-list .dl_item:hover { background:#eee; }

.dict-list .dl_item--pick { }

.dict-list .dl_item .cj-k  { line-height:1em; }

.dict-list .dl_t    { padding:12px 15px; font-weight:normal; }
.dict-list .dl_t .c { display:inline-block; font-size:22px; padding:5px 8px 3px; background:#e7e6e2; color:#000; }
.dict-list .dl_t .r { font-size:16px; padding:0; display:inline-block; margin:0 0 0 1em; color:#888; }

.dict-list .dl_t_menu { position:absolute; right:10px; top:15px; font-size:20px; color:#ccc; }
.dict-list .dl_item:hover .dl_t_menu { color:#888; }

   /* picked item */
.dict-list .dl_item--pick .dl_t_menu,
.dict-list .dl_item--pick:hover .dl_t_menu { color:#e2b437; }

  /* "known" word (contains known kanji) */
.dict-list .dl_t .known { background:#e6f2cd; color:#206717; }

  /* underline kanji reading */
.dict-list .dl_t u  { color:#f00; text-decoration:none; }

.dict-list .dl_d { padding:0 20px 1em; font:14px/1.3em Arial, sans-serif; color:#444; }

/* message when no words are found */
.dict-list_info { padding:1em 20px 1px; color:#838279; background:#fff; }

/* desktop & wider screens */
@media screen and (min-width:701px) {
 
  /* Review page only: fix the width inside the dialog from getting too wide */
  .yui-panel .dict-panel { width:400px; } 

}

</style>
