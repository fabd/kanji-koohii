<template>
<div>
  <div class="dict-panel" ref="refLoadingMask">

    <template v-if="isLoading">

      <div style="min-height:100px;"></div>
    
    </template>
    <template v-else-if="items.length">
  
      <div class="dict-list">
        <template v-for="$item in items">

        <div :class="[ 'dl_item', { 'dl_item--pick': $item.pick }]"  :key="$item.id" @click="onVocabPick($item)">

          <div class="dl_t">

            <div v-if="isKanjiReview" class="dl_t_menu">
              <i v-if="$item.pick === true" class="fa fa-star"></i>
              <i v-else class="far fa-star"></i>
            </div>

            <cjk_lang_ja className="c vocab_c" :html="$item.c"
              :class="{ known: $item.known }"></cjk_lang_ja>
            <cjk_lang_ja className="r vocab_r" :html="$item.fr"></cjk_lang_ja>
          </div>
          <div class="dl_d">
            {{ $item.g }}
          </div>

          <div if="isMenu">
            
          </div>
        
        </div>
        
        </template>
      </div>
      
    </template>
    <template v-else class="dict-list_info">

      <!-- items.length === 0 -->
      <p>There are no common words using this character.</p>

    </template>
 
  </div>

  <!-- (legacy code) "Close" button for mobile portait will be handled by KoohiiDialog if/when we implement that -->
  <div v-if="isMobile" class="uiBMenu">
    <div class="uiBMenuItem">
      <a class="uiFcBtnGreen JSDialogHide uiIBtn uiIBtnDefault" href="#"><span>Close</span></a>
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

import { KoohiiAPI, TRON } from '@lib/KoohiiAPI.js'

// comps
import cjk_lang_ja from './cjk_lang_ja.vue'

//mixins
import KoohiiFormat    from '@lib/mixins/KoohiiFormat.js'
import KoohiiLoading   from '@lib/mixins/KoohiiLoading.js'


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
    KoohiiFormat,
    KoohiiLoading
  ],

  props: {
    /**
     * Array of dict entries as obtained from dict lookup cache.
     * 
     *   DictEntry
     *
     *     (...see data/scripts/dict/dict_gen_cache.php ...)
     *
     *     pick:       {boolean}    user selected this item
     *     known       {boolean}    contains only known kanji
     *
     */
    items: { type: Array, default: function() { return [] } }
  },

  data() {
    return {
      isLoading: true,

      // kanji which corresponds to the last retrieved example words (cf load())
      ucsId: 0,

      // whether we have already requested them from server
      isSetKnownKanji: false,

      // a string containing all kanji known by the user
      knownKanji: ''
    }
  },

  computed: {
    KanjiReview() {
      return (window.App && window.App.KanjiReview)
    },

    isKanjiReview() {
      return !!this.KanjiReview
    },

    isMobile() {
      // (legacy code) cf. lib/front/corejs/ui/mobile.js
      return (window.innerWidth <= 720)
    }
  },

  methods: {

    // ! CAN NOT be computed because the dictionary is instanced *once*, while flashcard comp is recreated
    // 
    // @return {object} the KoohiiFlashcardKanji Vue instance, or false if not available (eg. Study page)
    getKanjiCard() {
      if (!this.isKanjiReview) { return false }
      let vmFlashcard = this.KanjiReview.oReview.getFlashcard()
      let inst = vmFlashcard.getChild()
      return inst
    },

    onVocabPick(item)
    {
      // console.log('onVocabPick "%s"', item.c)

      if (!this.isKanjiReview) {
        return
      }

      // add
      if (item.pick !== true)
      {
        // App.KanjiReview.oReview.curCard.cardData.v_on ...   
        this.koohiiloadingShow({ target: this.$refs.refLoadingMask })
        
        KoohiiAPI.setVocabForCard({ ucs: this.ucsId, dictid: item.id }, {
          then: (tron) => { this.onVocabPickResponse(tron, item) }
        })
      }
      // remove
      else
      {
        this.koohiiloadingShow({ target: this.$refs.refLoadingMask })

        KoohiiAPI.deleteVocabForCard({ ucs: this.ucsId }, {
          then: (tron) => { this.onVocabDeleteResponse(tron, item) }
        })
      }
    },

    onVocabDeleteResponse(tron, item)
    {
      this.koohiiloadingHide()

      if (tron.isSuccess()) {
        item.pick = false
        this.getKanjiCard().removeVocab(item)
        this.isKanjiReview && this.KanjiReview.toggleDictDialog();
      }
    },

    // @param {object} item           One of this.items[] which was clicked
    onVocabPickResponse(tron, item)
    {
      this.koohiiloadingHide()

      // success:  show vocab onto the flashcard, and close the dictionary
      if (tron.isSuccess())
      {
        // sets highlighted entry
        this.items.forEach((o) => { o.pick = false })
        item.pick = true

        const VocabPick = {
          compound: item.c,
          reading:  item.r,
          gloss:    item.g
        }
        this.getKanjiCard().setVocab(VocabPick)

        this.isKanjiReview && this.KanjiReview.toggleDictDialog();
      }
    },

    load(ucsId)
    {
      this.isLoading = true

      function doLoad() {
        this.koohiiloadingShow({ target: this.$refs.refLoadingMask })

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
          then: (tron) => {
            this.ucsId = ucsId
            this.onDictLoadResponse(tron)
          }
        })
      }


      this.$nextTick(doLoad)
    },

    onDictLoadResponse(tron, ucsId)
    {
      const props = tron.getProps()

      // console.log('onDictLoadResponse(%o)', props)
// return
      this.koohiiloadingHide()

      if (props.known_kanji) {
        this.knownKanji = props.known_kanji
        this.isSetKnownKanji = true
      }

      let items = this.setKnownItems(props.items, this.knownKanji)

      this.formatDictEntryArray(items)
      this.applyVocabPicks(items, props.picks)

      this.items = items

      this.isLoading = false
    },

    // {DictEntry}  items
    setKnownItems(items, knownKanji)
    {
      // if (this.known_kanji !== '') {
      //   console.log(' known_kanji : ' + this.known_kanji)
      // }
      const KNOWN_KANJI = knownKanji + HIRAGANA + KATAKANA + PUNCTUATION

      // a basic string search could be faster - it's a very small list though
      const regexp = new RegExp('^['+KNOWN_KANJI+']+$')
      items.forEach(item => {
        item.known = regexp.test(item.c)
      })

      // sort known vocab first
      let knownItems  = items.filter(o => o.known === true)
      let unkownItems = items.filter(o => o.known === false)
      let sortedItems = knownItems.concat(unkownItems)

      return sortedItems
    },

    formatDictEntryArray(items)
    {
      // assign a "formatted reading" for display, keep DictEntry's reading
      items.forEach((o) => {
        o.fr = this.koohiiformatReading(o.r)
      })
    },

    // set selected state, where 'picks' is an array of dictid's 
    applyVocabPicks(items, picks)
    {
      items.forEach((o) => { o.pick = picks.includes(o.id) })
    }

    // sortSelectedItems(items)
    // {
    //   let picks = items.filter(o => o.pick === true)
    //   let other = items.filter(o => o.pick !== true)  // undefined
    //   return picks.concat(other)
    // }
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
.dict-list .dl_item:hover .vocab_c { background:#fff; }

.dict-list .dl_item--pick { }

.dict-list .dl_item .cj-k  { line-height:1em; }

.dict-list .dl_t    { padding:12px 15px; font-weight:normal; font-size:26px; }
.dict-list .dl_t .c { font-size:1em; padding:5px 8px 3px; }
.dict-list .dl_t .r { font-size:0.8em; padding:0; display:inline-block; margin:0 0 0 1em; color:#543; }

.dict-list .dl_t_menu { position:absolute; right:10px; top:15px; font-size:20px; color:#ccc; }
.dict-list .dl_item:hover .dl_t_menu { color:#888; }

   /* picked item */
.dict-list .dl_item--pick .dl_t_menu,
.dict-list .dl_item--pick:hover .dl_t_menu { color:#e2b437; }

  /* "known" word (contains known kanji) */
.dict-list .dl_t .known,
.dict-list .dl_item:hover .known { background:#e6f2cd; color:#206717; }

.dict-list .dl_d { padding:0 20px 1em; font:14px/1.3em Arial, sans-serif; color:#444; }

/* message when no words are found */
.dict-list_info { padding:1em 20px 1px; color:#838279; background:#fff; }

/* desktop & wider screens */
@media screen and (min-width:701px) {
 
  /* Review page only: fix the width inside the dialog from getting too wide */
  .yui-panel .dict-panel { width:400px; } 

}

/* ===================================================== */
/* Visual formatting shared by DictList & FlashcardKanji */
/* ===================================================== */

  /* box-ing the compound for separation */
.vocab_c { display:inline-block; padding:3px 8px; border-radius:8px; background:#efeeed; color:#210; }

  /* the <em> surrounding tags originate in KoohiiFormat mixin, used by DictEntry.r and VocabPick.reading */
.vocab_r em { border-bottom:2px solid #ff4e4e; /*color:#ff4e4e;*/ font-style:normal; }

</style>
