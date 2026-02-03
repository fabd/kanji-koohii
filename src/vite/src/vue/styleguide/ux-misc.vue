<template>
  <div class="">
    <h2>Tailwind Playground</h2>
    <div class="tw-test mb-4">
      Using Tailwind's CSS custom properties in the CSS.
    </div>

    <div class="mb-4">
      <button class="group bg-gray-300 rounded-md px-2 py-2">
        <span>Icon</span>
        <span class="text-sky-800 group-hover:bg-sky-500"
          >group-hover example</span
        >
      </button>
    </div>

    <div class="mb-4 bg-teal-500 bg-red-500!"> Using !important (or not). </div>

    <div class="mb-4">
      Odd and even rows. nth-2:bg-red-500
      <table>
        <tr class="odd:bg-white even:bg-gray-200 nth-2:bg-red-500"
          ><td>Col 1</td><td>Col 2</td></tr
        >
        <tr class="odd:bg-white even:bg-gray-200 nth-2:bg-red-500"
          ><td>Col 1</td><td>Col 2</td></tr
        >
        <tr class="odd:bg-white even:bg-gray-200 nth-2:bg-red-500"
          ><td>Col 1</td><td>Col 2</td></tr
        >
        <tr class="odd:bg-white even:bg-gray-200 nth-2:bg-red-500"
          ><td>Col 1</td><td>Col 2</td></tr
        >
      </table>
    </div>

    <div class="mb-4">
      <span class="empty:hidden inline-block p-4 rounded bg-gray-300"></span>
    </div>

    <div class="mb-4">
      <label
        class="has-checked:bg-indigo-50 has-checked:text-indigo-900 has-checked:ring-indigo-200 dark:has-checked:bg-indigo-950 dark:has-checked:text-indigo-200 dark:has-checked:ring-indigo-900"
      >
        (G)Google Pay
        <input type="radio" class="checked:border-indigo-500 ..." />
      </label>
    </div>

    <div class="mb-4">
      <blockquote class="text-2xl font-semibold text-gray-900 italic">
        When you look
        <span
          class="relative inline-block before:absolute before:-inset-1 before:block before:-skew-y-3 before:bg-pink-500"
        >
          <span class="relative text-white">annoyed</span>
        </span>
        all the time, people think that you're busy.
      </blockquote>
    </div>

    <div class="mb-4">
      Style the file input:
      <input
        type="file"
        class="file:mr-4 file:rounded-full file:border-0 file:bg-violet-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-violet-700 hover:file:bg-violet-100 dark:file:bg-violet-600 dark:file:text-violet-100 dark:hover:file:bg-violet-500 ..."
      />
    </div>

    <ul role="list">
      <li class="[&.is-dragging]:cursor-grabbing is-dragging">{item}</li>
    </ul>

  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
  data() {
    return {};
  },
  computed: {},
  mounted() {},
});
</script>

<style>
.tw-test {
  background: var(--color-mint-500);
  color: var(--color-sky-600);
}
</style>
