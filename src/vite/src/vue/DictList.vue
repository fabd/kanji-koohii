<template>
  <div class="dict-list">
    <template v-for="$item in listItems" :key="$item.id">
      <div
        :class="['dl_item', { 'dl_item--pick': $item.selected }]"
        @click.stop="showSelected && onClickItem($item)"
      >
        <div class="dl_t">
          <div v-if="showSelected" class="dl_t_menu">
            <i v-if="$item.selected" class="fa fa-star"></i>
            <i v-else class="far fa-star"></i>
          </div>

          <!-- (#270): keep a white space between kanji & reading for users to ctrl-c -->
          <cjk-lang-ja class="c vocab_c" :html="$item.c" :class="{ known: $item.known }" />&nbsp;<cjk-lang-ja class="r vocab_r" :html="$item.fr" />
        </div>
        <div class="dl_d">{{ $item.g }}</div>

        <div if="isMenu"></div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
/**
 * A dumb component displaying a list of JMDICT entries.
 */
import { defineComponent, PropType } from "vue";
import { kkFormatReading } from "@lib/format";
import CjkLangJa from "@/vue/CjkLangJa.vue";

// FIXME : move to an import
// our simple regexp matching needs this so that vocab with okurigana is considered known
const HIRAGANA =
  "ぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをんゔゕゖ ゙ ゚゛゜ゝゞゟ";
const KATAKANA =
  "゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ";
const PUNCTUATION =
  "｟｠｡｢｣､･ｦｧｨｩｪｫｬｭｮｯｰｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜﾝﾞ";

type DictListEntryExt = DictListEntry & {
  // the compound contains only kanji present in "knownKanji" prop
  known?: boolean;
  // display item as selected by the user (eg. "starred" vocab)
  selected?: boolean;
  // formatted reading
  fr?: string;
};

export default defineComponent({
  name: "DictList",

  components: {
    CjkLangJa,
  },

  props: {
    // dict entries
    items: {
      type: Array as PropType<DictListEntry[]>,
      required: true,
    },
    // a string of known kanji, to highlight "known" compounds in the list
    knownKanji: {
      type: String,
      default: "",
    },
    // a list of entries ids to be shown as selected
    selectedItems: {
      type: Array as PropType<DictId[]>,
      default: () => [],
    },
    // show the selected state (eg. user "starred" an entry)
    showSelected: {
      type: Boolean,
      default: false,
    },
  },

  emits: ["click"],

  data() {
    return {
      //
    };
  },

  computed: {
    // formatted dict list items for templating
    listItems(): DictListEntryExt[] {
      let items = this.sortItemsByKnownKanji(this.items, this.knownKanji);
      this.formatDictItems(items);
      this.applySelectedItems(items, this.selectedItems);
      return items;
    },
  },

  methods: {
    onClickItem(item: DictListEntryExt) {
      console.log('onClickItem "%s"', item.c);
      this.$emit("click", { item, selected: item.selected });
    },

    sortItemsByKnownKanji(items: DictListEntryExt[], knownKanji: string) {
      const KNOWN_KANJI = knownKanji + HIRAGANA + KATAKANA + PUNCTUATION;

      // a basic string search could be faster - it's a very small list though
      const regexp = new RegExp("^[" + KNOWN_KANJI + "]+$");
      items.forEach((item) => {
        item.known = regexp.test(item.c);
      });

      // sort known vocab first
      let knownItems = items.filter((o) => o.known === true);
      let unkownItems = items.filter((o) => o.known === false);
      let sortedItems = knownItems.concat(unkownItems);

      return sortedItems;
    },

    // assign a "formatted reading" for display, keep DictEntry's reading
    formatDictItems(items: DictListEntryExt[]) {
      items.forEach((item) => {
        item.fr = kkFormatReading(item.r);
      });
    },

    // set selected state, where 'picks' is an array of dictid's
    applySelectedItems(items: DictListEntryExt[], selectedIds: DictId[]) {
      items.forEach((item) => {
        item.selected = selectedIds.includes(item.id);
      });
    },
  },
});
</script>

<style lang="scss">
@import "@/assets/sass/components/DictList.scss";
</style>
