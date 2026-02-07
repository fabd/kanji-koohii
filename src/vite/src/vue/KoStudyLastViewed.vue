<template>
  <div class="ko-Box ko-LastViewed">
    <h3>Last Viewed</h3>
    <ul class="ko-LastViewed-list mb-0">
      <li v-for="(item, i) in list" :key="item" class="ko-LastViewed-item">
        <a
          :href="createStudyUrl(item[1])"
          class="ko-LastViewed-link"
          :class="{
            'is-active': i === 0 && isActive,
          }"
        >
          <span class="ko-LastViewed-kan">{{ item[1] }}</span>
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

type TListItem = [number, string, string]; // index, kanji, keyword

let storage: Storage;
let store: TKoohiiLocalStore;

let studySearchUrl: string;

export default defineComponent({
  name: "KoStudyLastViewed",

  data() {
    return {
      // only show the current item highlight if the Study page matches a valid kanji
      isActive: false,

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
      for (const ucsId of store.lastViewed) {
        const index = RTK.getIndexForUCS(ucsId);
        const kanji = String.fromCodePoint(ucsId);
        const keyword = index ? RTK.getKeywordForUCS(ucsId) : "???";
        this.list.push([index, kanji, keyword]);
      }
    }
  },

  methods: {
    createStudyUrl(kanji: string) {
      const url = (studySearchUrl ??= kk_globals_get("STUDY_SEARCH_URL"));
      return `${url}/${kanji}`;
    },

    // KISS for now, we don't store other things on localStorage
    loadState() {
      const json = storage.getItem(KOOHII_LOCALSTORAGE_KEY);
      if (json) {
        try {
          const data = JSON.parse(json);
          store = data;
        } catch {
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
      } catch {
        console.warn("saveState() JSON.stringify() fails");
      }

      storage.setItem(KOOHII_LOCALSTORAGE_KEY, persistData);
    },

    update() {
      const currentUcsId = kk_globals_get("LASTVIEWED_UCS_ID", 0);

      this.isActive = currentUcsId > 0;

      // if this is 0, it means backend does not have the $kanjiData
      //  12000+ CJK chars are in the database, and have a "extended frame number"
      //  so this would likely be an invalid or extremely rare CJK char
      if (!currentUcsId) {
        return;
      }

      const pos = store.lastViewed.findIndex((ucsId) => ucsId === currentUcsId);

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
