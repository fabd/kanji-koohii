<?php
  // THIS IS A PLACEHOLDER DURING PAGE LOAD, IS REPLACED BY VUE COMPONENT
  // 
  // KEEP IN SYNC WITH THE VUE COMP.  (small details can be omitted, we want the main visuals on load)
  // 
  // use_helper('CJK');

  // (fab: OBSOLETE?) do the "multiple/edition" thing for original keywords
  function formatEditionKeyword($s) {
    $append = (strpos($s, rtkIndex::EDITION_SEPARATOR) > 0) ? '<br /><span class="edition">(multiple editions)</span>' : '';
    return esc_specialchars($s).$append;
  }
?>
  <div id="my-story" lang="ja">
    <div class="padding rtkframe">

      <div class="left">
        <div class="framenum" title="Frame number"><?= $kanjiData->framenum ?></div>
        <div class="kanji"><?= cjk_lang_ja($kanjiData->kanji) ?></div>
        <div class="strokecount" title="Stroke count">[<?= $kanjiData->strokecount ?>]<br/>
          <span title="ON reading" style="font-size:120%"><?= cjk_lang_ja($kanjiData->onyomi) ?></span>
        </div>
      </div>

      <div class="right">
        <div class="keyword">
          <span class="JSEditKeyword"><?= (null !== $custKeyword) ? esc_specialchars($custKeyword) : formatEditionKeyword($kanjiData->keyword) ?></span>
        </div>

        <div id="storybox">
          <div id="storyview">

            <div id="sv-textarea" class="bookstyle">
              <?= $formattedStory ?: '<div class="empty">[ click here to enter your story ]</div>' ?>
            </div>
            
            <!-- ditch the LEARNED button, etc. Won't show until the Vue comp is mounted, but minor visual flash -->

          </div>
        </div>
      </div><!-- /right -->

    </div>
    <div class="bottom"></div>
  </div>
