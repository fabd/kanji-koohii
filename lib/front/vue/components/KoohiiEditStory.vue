<template>

  <div style="min-height:100px;background:#ccc;">
   
  <form name="EditStory" method="post" action="/study/kanji/1">

    <input type="hidden" name="ucs_code" v-model="kanjiData.ucs_id">

    <!-- whether it is Study page, or the dialog in Flashcard Review mode -->
    <input v-if="isReviewMode" type="hidden" name="reviewMode" value="1" />
  
    <div id="my-story" lang="ja">

      <div class="padding rtkframe">

        <!-- left -->
        <div class="left">
          
          <div class="framenum" title="Frame number">{{ kanjiData.framenum }}</div>

          <div :class="{ kanji: true, onhover: isReviewMode }">
            <cjk_lang_ja>{{ kanjiData.kanji }}</cjk_lang_ja>
          </div>

          <div class="strokecount" title="Stroke count">[{{ kanjiData.strokecount }}]<br>{{ kanjiData.readings }}
            <span style="font-size:120%"><cjk_lang_ja>{{ kanjiData.onyomi }}</cjk_lang_ja></span>
          </div>
        
        </div>
        <!-- /left -->

        <!-- right -->
        <div class="right">
          
          <div class="keyword">
            <span class="JSEditKeyword" title="Click to edit the keyword" :data-url="editKeywordUrl">{{ kanjiData.keyword }}</span>
          </div>

          <div id="storybox">

            <div id="storyedit" style="display:none;">
            
              <textarea name="txtStory" id="frmStory"></textarea>

              <div class="controls valign">
                <div style="float:left;">
                  <input type="checkbox" name="chkPublic" value="1" id="chkPublic">
                  <label for="chkPublic">Share this story</label>
                </div>
                <div style="float:right;">
                  <input type="submit" name="doUpdate" value="Save changes" title="Save/Update story">
                  <input type="button" id="storyedit_cancel" value="Cancel" name="cancel" title="Cancel changes">
                </div>
                <div class="clear"></div>
              </div>
            </div>
                    
            <div id="storyview" style="display:block;">
              <div id="sv-textarea" class="bookstyle" title="Click to edit your story" style="display:block;"></div>
            </div>

          </div>

        </div>
        <!-- /right -->

        <div class="clear"></div>

      </div>
      <!-- /rtkframe -->

    </div>
    <!-- /#my-story -->

    <div class="bottom"></div>

  </form>

  </div>

</template>

<script>

import cjk_lang_ja from './cjk_lang_ja.vue'


export default {
  name: 'KoohiiEditStory',

  components: {
    cjk_lang_ja
  },

  props: {
    
    kanjiData: Object,

    custKeyword: String,

    reviewMode: Boolean

  },

  computed: {
    editKeywordUrl() {
      return '/study/editkeyword/id/' + this.kanjiData.ucs_id
    },

    isReviewMode() {
      return this.reviewMode === true
    }
  },

  data() {
    return {
    }
  },

  created() {
    Core.log('KoohiiEditStory::created()')

    Core.log('kanjiData %o', this.kanjiData)

  }

}
</script>

<style>
/**
 * EditStoryComponent CSS.
 *
 * Used on Study page & Edit Story Dialog (review page).
 *
 */

/* self story */
#my-story {
  position:relative; background:#fff; border:1px solid #E8E5C9; 
  box-shadow:0 1px 2px 0px rgba(170, 165, 130, 0.3); 
}
#my-story .rtkframe { border:none; padding:16px 17px 0; background:none; }
#my-story .bottom {
  width:100%; height:23px; overflow:hidden; clear:both;
  background:#fff;
  background: -webkit-linear-gradient(top,  #fff 0%,#f8f8f8 100%);
  background: linear-gradient(to bottom,  #fff 0%,#f8f8f8 100%);
}

#my-story .msg-relearned { color:#61932B; font-size:11px; padding:10px 5px 0; }

.rtkframe { clear:both; position:relative; background:white; padding:15px; }
.rtkframe .left        { float:left; width:68px; text-align:center; }
.rtkframe .right       { margin-left:82px; }

  /* left col */
.rtkframe .framenum    { font-size:14px; line-height:1em; margin:0 0 20px; }
.rtkframe .kanji       { width:100%; min-height:50px; margin:0 0 8px; font-size:50pt; line-height:1em; }
.rtkframe .strokecount { color:#8e8e8e; }

.rtkframe .keyword     { font:20px Georgia, Times New Roman; letter-spacing:2px; text-align:right; }
.rtkframe .keyword .edition { font-size:0.6em; }

.rtkframe #storybox    { padding:14px 0 0;  } /* story 'view' mode */
.rtkframe #sv-textarea { padding:5px; height:auto; min-height:100px; }

.rtkframe #sv-textarea.hover {  background:#f5f5f5; }

.rtkframe .bookstyle .empty { color:#888; }

 /* favorited story sign */
.rtkframe .favstory { display:inline-block; margin:1em 0 0; line-height:18px; padding:5px 0; color:#666; }
.rtkframe .favstory .ico { width:22px; height:18px; display:inline-block; background:url(/images/1.0/ico/study-story-actions.gif) no-repeat 0 0; }

#storyview .controls   { padding-right:16px; margin:12px 0 0; text-align:right; }

.rtkframe #storyedit { display:none; }
.rtkframe #storyedit textarea {
  width:100%; height:153px; padding:5px; border:1px solid #e8e5c9; background:#f5f5f5; box-sizing: border-box;
}
.rtkframe #storyedit .controls { margin:4px 0 0; height:25px; }

.viewtoggle a, .viewtoggle a:active, .viewtoggle a:visited, .viewtoggle a:hover { font-size:80%; font-weight:normal; }

 /* story content styling (My Stories and Shared Stories) */
.rtkframe .bookstyle {
  color:#100800; font-size:14px; line-height:1.5em; text-align:justify; /*ie fix expandbox*/word-wrap:break-word;
  /*-moz-osx-font-smoothing:auto; -webkit-font-smoothing:subpixel-antialiased;*/
}
.bookstyle em     { letter-spacing:0.05em; }
/*.bookstyle strong { }*/

.rtkframe .bookstyle a { font-weight:normal; text-decoration:none; }
.rtkframe .bookstyle a:hover { text-decoration:underline; }
.rtkframe .bookstyle .frnr { color:#484; font-family:sans-serif; }

#storyview .bookstyle { line-height:1.5em; }

/* Story popup (flashcard review page) kanji shows on mouseover */

#my-story .onhover { font-size:40pt; background:url(/images/1.0/hidden-kanji.gif) no-repeat 50% 50%; }
#my-story .onhover span { visibility:hidden; color:#fff; }
#my-story .onhover:hover { background:none; }
#my-story .onhover:hover span { visibility:visible; color:#000; background:#fff; }

/* Story edit form errors */
#my-story .formerrormessage { background:none; margin:0.5em 0; padding:0 5px; border:none; }


/* DIALOG mode */

 /* desktop dialog : use a fixed width (looks nicer), hide the big close button */
.rtk-skin-dlg #my-story { width:500px; border:none; }
.rtk-skin-dlg .editstory-close { display:none; }

 /* mobile dialog */
.rtk-mobl-dlg #my-story { box-shadow:none; border:none; /* remove decorations */ }
.rtk-mobl-dlg #my-story .rtkframe { padding:16px 10px 0; }
.rtk-mobl-dlg .left  { width:50px; display:none; }
.rtk-mobl-dlg #my-story .right { margin-left:0; }
.rtk-mobl-dlg #sv-textarea { border:1px solid #fff; background:#f5f5f5; border-radius:5px; } /* hint edit box by default for touch */

.rtk-mobl-dlg #JSEditStoryLoading { width:auto; }
</style>

