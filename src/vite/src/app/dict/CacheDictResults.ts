/**
 * A way of caching dict lookup results client side.
 * 
 *   Cancelled "Kanji Recognition" feature.
 *
 *   Could be useful later for SPA-style study page.
 */
import { getApi } from "@app/api/api";
// import type { GetDictCacheFor } from "@app/api/models";

// import * as CJK from "@/lib/cjk";

class CacheDictResults {
  //
  private static instance?: CacheDictResults;

  //
  private entries: { [key: TUcsId]: DictResults };

  constructor() {
    this.entries = {};
  }

  static getInstance() {
    this.instance ??= new CacheDictResults();
    return this.instance;
  }

  getCacheKeys() {
    return Object.keys(this.entries);
  }

  getResultsForUCS(ucsId: TUcsId): DictResults | null {
    return this.entries[ucsId] || null;
  }

  cacheResultsFor(chars: string, callback: { (items: DictResults): void }) {
    // const codes = CJK.toUnicode(chars);

/*test*/const ucsId = chars.charCodeAt(0);

    getApi()
      .legacy.getDictCacheFor(chars)
      .then((tron) => {
        if (tron.isSuccess()) {
          const { items } = tron.getProps();

          // FIXME  cache entries
          console.log("CDR :: set entries for ", ucsId, items);
          
          this.entries[ucsId] = items;

          callback(items);
        }
      });
  }
}

export default CacheDictResults;
