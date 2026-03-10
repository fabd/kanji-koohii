<template>
  <div class="ko-EditKeywordDlg body" ref="loadingMask">
    <div class="text-sm mb-4">
      <p>Press <kbd>Enter</kbd> to save and close the dialog.</p>

      <p v-if="isManagePage">
        Tip: press <kbd>TAB</kbd> to save and edit the next keyword.
      </p>

      <div v-if="formHasErrors()" class="formerrormessage">
        <span v-html="formGetErrors()"></span>
      </div>

      <form>
        <input
          ref="input"
          type="text"
          name="keyword"
          v-model="keyword"
          class="form-control bg-amber-100 focus:bg-amber-100"
          autocomplete="off"
          @keydown="onKeyDown($event)"
        />

        <div class="mt-4 flex items-center justify-end">
          <div class="mr-auto pl-2">
            Characters left:
            <koohii-chars-left
              :text="keyword"
              :max-length="maxLength"
              :warning-limit="5"
            />
          </div>

          <a
            href="#"
            class="inline-block text-[#f37200] hover:text-[#f37200] mr-4"
            @click.stop.prevent="onReset"
          >
            Reset
          </a>

          <input
            type="submit"
            name="commit"
            value="Save"
            class="ko-Btn ko-Btn--success ko-Btn--small"
            @click.prevent="onSubmit"
          />
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, type PropType } from "vue";
import { getApi } from "@app/api/api";
import { type TronInst } from "@lib/tron";
import { type PostUserKeywordResponse } from "@app/api/models";

import KoohiiCharsLeft from "@/vue/KoohiiCharsLeft.vue";
import KoohiiLoading from "@/vue/KoohiiLoading";

export default defineComponent({
  name: "KoEditKeyword",

  components: {
    KoohiiCharsLeft,
  },

  data() {
    return {
      keyword: "",
      formErrors: [] as string[],
      pressedTab: false, // true if submit via tab key
    };
  },

  props: {
    ucsId: { type: Number, required: true },
    origKeyword: { type: String, required: true },
    userKeyword: { type: String, required: true },
    maxLength: { type: Number, required: true },
    isManagePage: { type: Boolean, required: true },
    onSuccess: {
      type: Function as PropType<(keyword: string, tabKey?: boolean) => void>,
      required: true,
    },
  },

  mounted() {
    this.keyword = this.userKeyword;
    this.focusInput();
  },

  methods: {
    // note! this is also called by EditKeywordDialog::show()
    focusInput() {
      (this.$refs.input as HTMLInputElement).focus();
    },

    formGetErrors(): string {
      const errors = this.formErrors;
      return errors.length
        ? `<span>${errors.join("</span><span>")}</span>`
        : "";
    },

    formHasErrors() {
      return this.formErrors.length > 0;
    },

    // listener for the TAB key (chain edit on the Manage page)
    onKeyDown(ev: Event) {
      const kbdEvent = <KeyboardEvent>ev;

      // TAB key
      if (kbdEvent.keyCode === 9 && !this.pressedTab) {
        ev.stopPropagation();
        ev.preventDefault(); // dont move the focus
        this.pressedTab = true;
        this.onSubmit();
        return false;
      }

      return true;
    },

    onReset() {
      // reset the keyword to the original keyword and focus input
      this.keyword = this.origKeyword;
      this.focusInput();
    },

    onSubmit() {
      KoohiiLoading.show({ target: this.$refs.loadingMask as HTMLElement });

      getApi()
        .legacy.postUserKeyword(this.ucsId, this.keyword)
        .then((tron: TronInst<PostUserKeywordResponse>) => {
          KoohiiLoading.hide();

          this.formErrors = tron.getErrors();

          const props = tron.getProps();
          if (props.keyword) {
            this.keyword = props.keyword;
          }

          // close the dialog after succesful update
          if (!tron.hasErrors()) {
            this.onSuccess(this.keyword, this.pressedTab);
          }

          // after an error, stop the chain editing (Manage page)
          this.pressedTab = false;
        });
    },
  },
});
</script>
