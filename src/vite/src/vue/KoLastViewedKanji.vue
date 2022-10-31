<template>
  <div class="ko-Box">
    <h3>Last Viewed</h3>
    <div v-for="item in list" :key="item">
      <div class="flex items-center">
        <span class="w-[40px]">{{ item[0] }}</span>
        <span class="font-serif">{{ item[1] }}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

import * as RTK from "@/lib/rtk";

const KOOHII_LOCALSTORAGE_KEY = "lastViewedKanji";
const STORE_VERSION = 20221028;

type TKoohiiLocalStore = {
  version: number;
  lastViewed: TUcsId[];
};

type TListItem = [number, string];

let storage: Storage;
let store: TKoohiiLocalStore | null = null;

export default defineComponent({
  name: "KoLastViewedKanji",

  data() {
    return {
      list: [] as TListItem[],
    };
  },

  created() {
    this.initStore();

    this.list = [];
    if (store) {
      for (let ucsId of store?.lastViewed) {
        const index = RTK.getIndexForUCS(ucsId);
        const keyword = index ? RTK.getKeywordForUCS(ucsId) : "???";
        this.list.push([index, keyword]);
      }
    }
  },

  methods: {
    // KISS for now, we don't store other things on localStorage
    initStore() {
      storage = window.localStorage;

      let json = storage.getItem(KOOHII_LOCALSTORAGE_KEY);
      if (json) {
        try {
          let data = JSON.parse(json);
          store = data;
        } catch (e) {
          // shouldn't happen - but just in case, avoid breaking the rest of the Study page
          console.warn("JSON.parse() error");
        }
      }

      if (!store || store.version !== STORE_VERSION) {
        store = {
          version: STORE_VERSION,
          lastViewed: [19968, 19978],
        };
      }
    },
  },
});
</script>
