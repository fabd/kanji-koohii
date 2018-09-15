<template>
  
  <transition name="kk-loading-fade">
    <div
      v-show="visible"
      class="kk-loading-mask"
      :style="{ backgroundColor: background || '' }">
      <div class="kk-loading-spinner">
        <div class="kk-html-spinner"></div>
      </div>
    </div>
  </transition>

</template>

<script>

import { classList} from 'lib/koohii/dom.js'

export default {

  data() {
    return {
      target: null,

      background: null,
      
      visible: false
    };
  },

  methods: {
    close() {
      // afterLeave(this, () => {
        const target = this.target
        
        if (target) {
          classList.remove(target, 'kk-loading-target--relative')
        }

        // remove vue from page
        if (this.$el && this.$el.parentNode) {
          this.$el.parentNode.removeChild(this.$el);
        }

        this.$destroy()
      // }, 300)

      // this.visible = false
    }

    /*
     @after-leave="onAfterLeave"
     
    onAfterLeave() {
      console.log('KoohiiLoading :: after-leave ()')
      
      const target = this.target
      classList.remove(target, 'kk-loading-target--relative')
      if (this.$el && this.$el.parentNode) {
        this.$el.parentNode.removeChild(this.$el)
      }

      this.visible = false

      this.$destroy()
    }
    */
  }

}

</script>

<style>
.kk-loading-target--relative {
  /* class added by the mixin to the target element covered by the mask (the mask being appended as a child) */
  position:relative;
}

.kk-loading-mask {
  /* the mask is appended asa child, the parent element is position:relative, this covers the area */
  position: absolute;
  z-index: 2000;
  background-color: hsla(0,0%,100%,.9);
  margin: 0;
  top: 0; right: 0; bottom: 0; left: 0;
  transition: opacity .3s;
}

.kk-loading-spinner {
  top: 50%; margin-top: -21px;
  width: 100%;
  text-align: center;
  position: absolute;
}

/* simple css spinner with border trick */
.kk-html-spinner {
  display:inline-block;
  width:40px; height:40px;
  border:4px solid #5cba5b;
  border-top:4px solid white;
  border-radius:50%;

  /* animation */
  transition-property: transform;
  animation-name: kk-html-spinner-rotate;
  animation-duration: 1.2s; 
  animation-iteration-count: infinite;
  animation-timing-function: linear;
}

@keyframes kk-html-spinner-rotate {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

/* vue transition */
.kk-loading-fade-enter,
.kk-loading-fade-leave-active {
  opacity: 0;
}

</style>