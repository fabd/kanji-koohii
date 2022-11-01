<template>
  <div class="ko-Box ko-LastViewed">
    <h3>Last Viewed</h3>
    <ul class="ko-LastViewed-list mb-0">
      <li v-for="(item, i) in list" :key="item" class="ko-LastViewed-item">
        <a
          :href="createStudyUrl(item[1])"
          class="ko-LastViewed-link"
          :class="{
            'is-active': i === 0,
          }"
        >
          <span class="ko-LastViewed-idx">{{ item[0] }}</span>
          <span class="ko-LastViewed-kwd">{{ item[2] }}</span>
        </a>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { kk_globals_get } from "@app/root-bundle";
import * as RTK from "@/lib/rtk";

const KOOHII_LOCALSTORAGE_KEY = "lastViewedKanji";
const STORE_VERSION = 20221028;
const MAX_ITEMS = 10;

type TKoohiiLocalStore = {
  version: number;
  lastViewed: TUcsId[];
};

type TListItem = [number, TUcsId, string]; // index, ucs, keyword

let storage: Storage;
let store: TKoohiiLocalStore;

const STUDY_SEARCH_URL = kk_globals_get("STUDY_SEARCH_URL");

export default defineComponent({
  name: "KoStudyLastViewed",

  data() {
    return {
      list: [] as TListItem[],
    };
  },

  created() {
    storage = window.localStorage;
    this.loadState();

    this.update();

    this.saveState();

    this.list = [];
    if (store) {
      for (let ucsId of store?.lastViewed) {
        const index = RTK.getIndexForUCS(ucsId);
        const keyword = index ? RTK.getKeywordForUCS(ucsId) : "???";
        this.list.push([index, ucsId, keyword]);
      }
    }
  },

  methods: {
    createStudyUrl(ucsId: TUcsId) {
      const kanji = String.fromCodePoint(ucsId);
      return `${STUDY_SEARCH_URL}/${kanji}`;
    },

    // KISS for now, we don't store other things on localStorage
    loadState() {
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
          lastViewed: [],
        };
      }

      // just in case we update the limit later
      this.trim();
    },

    saveState() {
      // the parts of the store which we want to persist
      const data: TKoohiiLocalStore = store;

      let persistData = "";

      try {
        persistData = JSON.stringify(data);
      } catch (e) {
        console.warn("saveState() JSON.stringify() fails");
      }

      storage.setItem(KOOHII_LOCALSTORAGE_KEY, persistData);
    },

    update() {
      const currentUcsId = kk_globals_get("LASTVIEWED_UCS_ID", 0);

      if (!currentUcsId) {
        return;
      }

      let pos = store.lastViewed.findIndex((ucsId) => ucsId === currentUcsId);

      if (pos < 0) {
        store.lastViewed.unshift(currentUcsId);
        this.trim();
      } else {
        store.lastViewed.splice(pos, 1);
        store.lastViewed.unshift(currentUcsId);
      }
    },

    trim() {
      if (store.lastViewed.length > MAX_ITEMS) {
        store.lastViewed = store.lastViewed.slice(0, MAX_ITEMS);
      }
    },
  },
});
</script>
