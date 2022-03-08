<template>
  <transition name="newstory-fadein" appear>
    <div class="sharedstory rtkframe" lang="ja">
      <div class="sharedstory_author" v-html="profileLink"></div>

      <div class="bookstyle">
        <div class="story" v-html="story"></div>
      </div>

      <div class="sharedstory_meta">
        <div class="lastmodified"
          ><i class="far fa-clock"></i> NEW</div
        >

        <div :id="divId" class="sharedstory-actions JsAction">&nbsp;</div>
      </div>
    </div>
  </transition>
</template>

<script lang="ts">
/**
 * Placeholder component, until maybe/someday we refactor Shared Stories list & items to Vue.
 *
 * Until then, dynamically add a "new story" template, for visual feedback.
 *
 * If we refactor later we will have eg.
 *
 *    KoohiiSharedStoriesList.vue
 *      KoohiiSharedStory.vue
 *
 *      ... with an option (prop) to display a newly published/updated story,
 *          * no vote / report / copy button
 *          * "enter" animation
 *          * maybe a "unpublish" buttoN?
 */
import { defineComponent } from "vue";

export default defineComponent({
  props: {
    profileLink: { type: String, required: true },
    story: { type: String, required: true },

    // a unique id per user+story that's output by _SharedStores.php, lets us find the div easily
    divId: { type: String, required: true },
  },
});
</script>

<style>
/* fade in the published story for visual feedback */
.newstory-fadein-enter-from {
  opacity: 0;
  transform: translateX(20px);
}
.newstory-fadein-enter-active {
  transition: opacity 2s, transform 1s;
}
</style>
