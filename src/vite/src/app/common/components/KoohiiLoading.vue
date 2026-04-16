<template>
  <transition name="ko-loading-fade">
    <div
      v-show="isVisible"
      class="ko-loading-mask"
      :style="{ backgroundColor: background || '' }"
    >
      <div class="ko-loading-spinner">
        <div class="ko-html-spinner"></div>
      </div>
    </div>
  </transition>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  data() {
    return {
      isVisible: false,
      originalPosition: "",
    };
  },

  props: {
    target: { type: Element, required: true },
    background: { type: String, default: "" },
    visible: { type: Boolean, default: false },
  },

  // unmounted() {
  //   console.log('KoohiiLoading::unmounted()');
  // },

  methods: {
    setVisible(value: boolean) {
      this.isVisible = value;
    },
  },
});
</script>

<style>
/* class added to the parent element of KoohiiLoading  */
.ko-loading-target--relative {
  /* for the absolute positioning of the loading mask */
  position: relative;
  /* set min-height for when container is empty (eg. ajax loading) */
  min-height: 100px;
}

.ko-loading-mask {
  /* the mask is appended asa child, the parent element is position:relative, this covers the area */
  position: absolute;
  z-index: calc(var(--z-base) + 1);
  background-color: var(--ko-loading-bg, #fff8);
  border-radius: 3px;
  margin: 0;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  transition: opacity 0.3s;
}

.ko-loading-spinner {
  top: 50%;
  margin-top: -21px;
  width: 100%;
  text-align: center;
  position: absolute;
}

/* simple css spinner with border trick */
.ko-html-spinner {
  display: inline-block;
  width: 40px;
  height: 40px;
  border: 4px solid #c8e0ab;
  border-top: 4px solid white;
  border-radius: 50%;

  /* animation */
  transition-property: transform;
  animation-name: ko-html-spinner-rotate;
  animation-duration: 1.2s;
  animation-iteration-count: infinite;
  animation-timing-function: linear;
}

@keyframes ko-html-spinner-rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* vue transition */
.ko-loading-fade-enter-from,
.ko-loading-fade-leave-active {
  opacity: 0;
}
</style>
