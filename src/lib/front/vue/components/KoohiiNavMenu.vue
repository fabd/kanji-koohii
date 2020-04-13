<template>
  <ul class="k-nav-menu">
    <template v-for="$tm in menu.items">
      <koohii-nav-menu-item :key="$tm.id" :sm="$tm" />
    </template>
  </ul>
</template>

<style>
/* the styles are kept in KoohiiNavMenuItem */
</style>

<script>
import $$ from "@lib/koohii/dom";
import KoohiiNavMenuItem from "./KoohiiNavMenuItem.vue";

export default {
  name: "KoohiiNavMenu",

  components: {
    KoohiiNavMenuItem,
  },

  props: {
    menu: { type: Object, required: true },
  },

  data() {
    return {
      // references to top level KNavMenuItem's
      menuItems: {},

      initted: false,
    };
  },

  computed: {
    defaultOpened() {
      return this.menu.opened;
    },
  },

  created() {
    // console.log('KoohiiNavMenu::created()')
  },

  mounted() {
    // console.log('KoohiiNavMenu::mounted()')
    // Vue.nextTick(() => {    })
  },

  methods: {
    handleSelect(id, item) {
      if (!item.hasChildren) {
        return;
      }

      item.isOpened() ? item.close() : item.open();

      // accordion
      for (let itemId in this.menuItems) {
        if (itemId !== id) {
          let menuItem = this.menuItems[itemId].oMenuItem;
          // console.log('%s %o', itemId, menuItem.isOpened())
          if (item.isOpened() && menuItem.isOpened()) menuItem.close();
        }
      }
    },

    initCollapsedItems() {
      if (this.initted) {
        return;
      }

      // compute height of the collapsed items for proper "expand" animation
      // console.log('initCollapsedItems()')

      for (let itemId in this.menuItems) {
        let { oMenuItem, elHead } = this.menuItems[itemId];

        // store the height of the collapsed content on initial render
        if (oMenuItem.hasChildren) {
          oMenuItem.contentHeight = elHead.scrollHeight;
          // console.log('mounted(%s) scroll height %d', oMenuItem.sm.label, oMenuItem.contentHeight)

          if (oMenuItem.opened) {
            $$(elHead).css("height", oMenuItem.contentHeight + "px");
          } else {
            $$(elHead).css({ height: "0", display: "none" });
          }
        }
      }

      this.initted = true;
    },
  },
};
</script>
