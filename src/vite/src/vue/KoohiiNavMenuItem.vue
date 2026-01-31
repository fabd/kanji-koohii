<template>
  <li
    :class="{
      'k-nav-menu-item': true,
      'is-opened': opened,
    }"
  >
    <div
      v-once
      class="k-nav-menu-item__label"
      @click="onMenuItemClick($event, sm.id)"
    >
      <i v-if="sm.icon" :class="['fa', sm.icon]"></i><span v-html="sm.label" />
    </div>
    <div ref="contentWrap" class="k-fx-collapse__wrap">
      <div ref="content">
        <ul v-if="sm.children.length" class="k-nav-menu">
          <koohii-nav-menu-item
            v-for="$item in sm.children"
            :key="$item.id"
            :sm="$item"
          />
        </ul>
      </div>
    </div>
  </li>
</template>

<script lang="ts">
import { defineComponent, type ComponentPublicInstance } from "vue";
import $$, { px } from "@lib/dom";
import KoohiiNavMenu from "@/vue/KoohiiNavMenu.vue";

// fix for circular reference (can't type return value of rootMenu())
type TKoohiiNavMenu = TVueInstanceOf<typeof KoohiiNavMenu>;

export default defineComponent({
  name: "KoohiiNavMenuItem",

  props: {
    sm: { type: Object, required: true },
  },

  data() {
    return {
      opened: false,

      contentHeight: 0, // set by parent component
    };
  },

  computed: {
    hasChildren(): boolean {
      return this.sm.children && this.sm.children.length > 0;
    },

    rootMenu() {
      var parent = this.$parent as ComponentPublicInstance;
      while (parent.$options.name !== "KoohiiNavMenu") {
        parent = parent.$parent!;
      }
      return parent;
    },
  },

  created() {
    // console.log('KoohiiNavMenuItem::created()')

    //this.rootMenu.menuItems[this.sm.id] = this;
    if (!this.sm.children) {
      this.sm.children = [];
    }

    // set initial open state
    this.opened =
      (this.rootMenu as TKoohiiNavMenu).defaultOpened === this.sm.id;

    // console.log('created menu item %s opened %o', this.sm.id, this.opened);
  },

  mounted() {
    // top level items
    if (this.rootMenu === this.$parent) {
      (this.rootMenu as TKoohiiNavMenu).menuItems[this.sm.id] = {
        oMenuItem: this,
        elHead: this.refContentWrap(),
      };
    }
  },

  methods: {
    isOpened() {
      return this.opened;
    },

    refContentWrap(): HTMLElement {
      return this.$refs.contentWrap as HTMLElement;
    },

    open() {
      // console.log('open(%s)', this.sm.id)

      this.opened = true;

      const $elWrap = $$(this.refContentWrap());

      //$(elContentWrap).setStyles({ display: 'block', height: 0 })

      $elWrap.css({ display: "block", height: "0" });

      setTimeout(() => {
        // console.log('transition to %d', this.contentHeight)

        // transition:height
        $elWrap.css("height", px(this.contentHeight));
        $elWrap.once("transitionend", () => {
          // console.log('transitionend() this is this %o', this === that)
          $elWrap.css("height", "auto");
        });
      }, 10);
    },

    close() {
      // console.log('close(%s)', this.sm.id)

      this.opened = false;

      const $elWrap = $$(this.refContentWrap());
      this.contentHeight = $elWrap[0].scrollHeight;
      // console.log('elWrap scroll height is  %d', this.contentHeight)

      $elWrap.css("height", px(this.contentHeight));

      setTimeout(() => {
        // transition:height
        $elWrap.css("height", "0");
        $elWrap.once("transitionend", () => {
          //if (that.opened) return;
          $elWrap.css("display", "none");
        });
      }, 10);
    },

    onMenuItemClick(event: Event) {
      // console.log('onMenuItemClick(%s)', id);

      // handle folder
      if (this.hasChildren) {
        (this.rootMenu as TKoohiiNavMenu).handleSelect(this.sm.id, this);

        event.preventDefault();
        event.stopPropagation();
        return;
      }

      // handle menu item (no children)
      // ...
      // (event passing through)
    },
  },
});
</script>
