<template>
<div>
  <transition name="slideleft" @enter="slideEnter">
    <div class="aside" v-show="show">
 
      <div class="aside_close" @click="show = false">
        <i class="aside_close_icon fa fa-bars"></i>
      </div>
       
      <div class="aside_nav_content" ref="navContent"></div>

    </div>
  </transition>
  <transition name="mask" @before-enter="maskBeforeEnter" @after-leave="maskAfterLeave">
    <div class="aside-backdrop" v-if="show" @touchmove.stop.prevent @click="show = false"></div>
  </transition>
</div>

</template>

<script>
import $ from '../lib/coreJS.js'
import {getScrollBarWidth} from '../lib/utils.js'

export default {

  data() {
    return {
      // default options for Aside
      show:     false,

      // options for nav component
      navOptions:  null
    }
  },

  watch: {
    show(value) {
      // console.log('wtach show %o', this.show)
      if (value) {


      }
    } 
  },

  methods: {
    slideEnter() {
      // console.log('slideEnter()')
    },


    maskBeforeEnter(el) {
      // console.log("maskBeforeEnter()")
      const $body = $(document.body)
      const scrollBarWidth = getScrollBarWidth()

      this.bodyOverflow = $body.css('overflow')
      $body.css('overflow', 'hidden')

      this.bodyPaddingRight = $body.css('padding-right')
      if (scrollBarWidth !== 0) {
        $body.css('padding-right', scrollBarWidth + 'px')
      }

    },
    maskAfterLeave() {
      // console.log("maskAfterLeave()")

      $(document.body).css({
        'padding-right': this.bodyPaddingRight,
        'overflow':      this.bodyOverflow
      })
    }
  },

  // life cycle
  // created() { core.log('Aside created()') },
  mounted() {
    // console.log('Aside mounted()')

  }
}
</script>

<style>
.aside {
  position: fixed;
  top: 0;
  bottom: 0;
  z-index: 1002;
  overflow: auto;
  background: #2b3034;
  width:280px;

  /* aside__left*/
  left:0;
  right:auto;
}

 /* the close button ismade to overlap exactly the one appearing in the site's top bar */
.aside_close {
  position:absolute; left:0; top:0;
  width:50px; height:46px;
  font-size:24px; text-align:center; vertical-align:middle; color:#616161;
  cursor:pointer;
}
.aside_close_icon { padding-top:10px; }

.aside_nav_content { margin:72px 0 0; }

.slideleft-enter { transform:translateX(-100%); }
.slideleft-enter-active { animation:.2s ease-out slideleft-in; }
.slideleft-leave-active { animation:.2s ease-in  slideleft-out; }

@keyframes slideleft-in {
    0% { transform: translateX(-100%); opacity: 0; }
  100% { transform: translateX(0);     opacity: 1; }
}
@keyframes slideleft-out {
    0% { transform: translateX(0);     opacity: 1; }
  100% { transform: translateX(-100%); opacity: 0; }
}
.aside:focus {
  outline: 0
}

.aside-backdrop {
  position: fixed;
  top: 0; left: 0;
  right: 0; bottom: 0;
  z-index: 1001;
  background-color: #000;

  opacity: 0.5;
  transition: opacity .3s ease;
}

.mask-enter, .mask-leave-active {
  opacity: 0
}

@media (max-width: 360px) {
  /* reduce panel size on smaller screens */
  .aside { width:250px; }
}
</style>