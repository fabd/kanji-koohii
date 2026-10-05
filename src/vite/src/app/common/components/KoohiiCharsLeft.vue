<template>
  <span
    :class="[
      'ko-charsleft',
      { 'ko-charsleft--invalid': isTooLong },
      { 'ko-charsleft--warning': isWarning },
    ]"
    >{{ charsLeft }}</span
  >
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  name: "KoohiiCharsLeft",

  props: {
    text: { type: String, required: true },
    maxLength: { type: Number, required: true },
    warningLimit: { type: Number, default: 0 }, // 0 means "no warning"
  },

  data() {
    return {};
  },

  computed: {
    charsLeft(): number {
      return this.maxLength - this.text.length;
    },

    isTooLong(): boolean {
      return this.charsLeft < 0;
    },

    isWarning(): boolean {
      return (
        this.warningLimit !== 0 &&
        this.charsLeft >= 0 &&
        this.charsLeft <= this.warningLimit
      );
    },
  },
});
</script>

<style>
/* KoohiiCharsLeft styles */

.ko-charsleft {
  display: inline-block;
  padding: 1px 4px;
  border-radius: 3px;
  color: var(--color-neutral-400);
}
.ko-charsleft--invalid {
  background-color: var(--clr-ff7876);
  color: var(--color-white);
  font-weight: bold;
}
.ko-charsleft--warning {
  color: var(--clr-ff7876);
  font-weight: bold;
}
</style>
