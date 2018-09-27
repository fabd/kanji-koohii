<template>
<div>

  <div v-if="isLoading">
    <div class="dict-panel" ref="maskArea">
      <div style="min-height:100px;"></div>
    </div>
  </div>

  <div v-if="!isLoading && items.length" class="dict-panel">

    <dl class="dict-list">
      <template v-for="$item in items">

        <dt>
          <cjk_lang_ja className="c" :html="$item.compound"
            :class="{ known: $item.known }"></cjk_lang_ja>
          <cjk_lang_ja className="r" :html="$item.reading"></cjk_lang_ja>
        </dt>
        <dd>{{ $item.glossary }}</dd>

      </template>
    </dl>

  </div>

  <div v-if="!isLoading && items.length === 0" class="dict-list_info">
      <p>There are no common words using this character.</p>
    </div>
  </div>

</div>
</template>

<script>
/**
 * The dictionary list as seen in Study pages, and dictionary lookup in flashcard reviews.
 *
 * In the future, this list may allow to toggle bookmarking any individual entry, creating
 * a list of vocabulary for the user.
 * 
 */

import { KoohiiAPI, TRON } from 'lib/KoohiiAPI.js'

// comps
import cjk_lang_ja from './cjk_lang_ja.vue'

//mixins
import KoohiiLoading       from 'lib/mixins/KoohiiLoading.js'


// for debugging
// const KNOWN = '上以向'
//const KNOWN = '一二三四五六七八九十口日月田目古吾冒朋明唱晶品呂昌早旭世胃旦胆亘凹凸旧自白百中千舌升昇丸寸肘専博占上下';//卓朝嘲只貝唄貞員貼見児元頁頑凡負万句肌旬勺的首乙乱直具真工左右有賄貢項刀刃切召昭則副別丁町可頂子孔了女好如母貫兄呪克小少大多夕汐外名石肖硝砕砂妬削光太器臭嗅妙省厚奇川州順水氷永泉腺原願泳沼沖汎江汰汁沙潮源活消況河泊湖測土吐圧埼垣填圭封涯寺時均火炎煩淡灯畑災灰点照魚漁里黒墨鯉量厘埋同洞胴向尚字守完宣宵安宴寄富貯木林森桂柏枠梢棚杏桐植椅枯朴村相机本札暦案燥未末昧沫味妹朱株若草苦苛寛薄葉模漠墓暮膜苗兆桃眺犬状黙然荻狩猫牛特告先洗介界茶脊合塔王玉宝珠現玩狂旺皇呈全栓理主注柱金銑鉢銅釣針銘鎮道導辻迅造迫逃辺巡車連軌輸喩前煎各格賂略客額夏処条落冗冥軍輝運冠夢坑高享塾熟亭京涼景鯨舎周週士吉壮荘売学覚栄書津牧攻敗枚故敬言警計詮獄訂訃討訓詔詰話詠詩語読調談諾諭式試弐域賊栽載茂戚成城誠威滅減蔑桟銭浅止歩渉頻肯企歴武賦正証政定錠走超赴越是題堤建鍵延誕礎婿衣裁装裏壊哀遠猿初巾布帆幅帽幕幌錦市柿姉肺帯滞刺制製転芸雨雲曇雷霜冬天妖沃橋嬌立泣章競帝諦童瞳鐘商嫡適滴敵匕叱匂頃北背比昆皆楷諧混渇謁褐喝葛旨脂詣壱毎敏梅海乞乾腹複欠吹炊歌軟次茨資姿諮賠培剖音暗韻識鏡境亡盲妄荒望方妨坊芳肪訪放激脱説鋭曽増贈東棟凍妊廷染燃賓歳県栃地池虫蛍蛇虹蝶独蚕風己起妃改記包胞砲泡亀電竜滝豚逐遂家嫁豪腸場湯羊美洋詳鮮達羨差着唯堆椎誰焦礁集准進雑雌準奮奪';
//確午許歓権観羽習翌曜濯曰困固錮国団因姻咽園回壇店庫庭庁床麻磨心忘恣忍認忌志誌芯忠串患思恩応意臆想息憩恵恐惑感憂寡忙悦恒悼悟怖慌悔憎慣愉惰慎憾憶惧憧憬慕添必泌手看摩我義議犠抹拭拉抱搭抄抗批招拓拍打拘捨拐摘挑指持拶括揮推揚提損拾担拠描操接掲掛捗研戒弄械鼻刑型才財材存在乃携及吸扱丈史吏更硬梗又双桑隻護獲奴怒友抜投没股設撃殻支技枝肢茎怪軽叔督寂淑反坂板返販爪妥乳浮淫将奨采採菜受授愛曖払広勾拡鉱弁雄台怠治冶始胎窓去法会至室到致互棄育撤充銃硫流允唆出山拙岩炭岐峠崩密蜜嵐崎崖入込分貧頒公松翁訟谷浴容溶欲裕鉛沿賞党堂常裳掌皮波婆披破被残殉殊殖列裂烈死葬瞬耳取趣最撮恥職聖敢聴懐慢漫買置罰寧濁環還夫扶渓規替賛潜失鉄迭臣姫蔵臓賢腎堅臨覧巨拒力男労募劣功勧努勃励加賀架脇脅協行律復得従徒待往征径彼役徳徹徴懲微街桁衡稿稼程税稚和移秒秋愁私秩秘称利梨穫穂稲香季委秀透誘稽穀菌萎米粉粘粒粧迷粋謎糧菊奥数楼類漆膝様求球救竹笑笠笹箋筋箱筆筒等算答策簿築篭人佐侶但住位仲体悠件仕他伏伝仏休仮伎伯俗信佳依例個健側侍停値倣傲倒偵僧億儀償仙催仁侮使便倍優伐宿傷保褒傑付符府任賃代袋貸化花貨傾何荷俊傍俺久畝囚内丙柄肉腐座挫卒傘匁以似併瓦瓶宮営善膳年夜液塚幣蔽弊喚換融施旋遊旅勿物易賜尿尼尻泥塀履屋握屈掘堀居据裾層局遅漏刷尺尽沢訳択昼戸肩房扇炉戻涙雇顧啓示礼祥祝福祉社視奈尉慰款禁襟宗崇祭察擦由抽油袖宙届笛軸甲押岬挿申伸神捜果菓課裸斤析所祈近折哲逝誓斬暫漸断質斥訴昨詐作雪録剥尋急穏侵浸寝婦掃当彙争浄事唐糖康逮伊君群耐需儒端両満画歯曲曹遭漕槽斗料科図用庸備昔錯借惜措散廿庶遮席度渡奔噴墳憤焼暁半伴畔判拳券巻圏勝藤謄片版之乏芝不否杯矢矯族知智挨矛柔務霧班帰弓引弔弘強弥弱溺沸費第弟巧号朽誇顎汚与写身射謝老考孝教拷者煮著箸署暑諸猪渚賭峡狭挟頬追阜師帥官棺管父釜交効較校足促捉距路露跳躍践踏踪骨滑髄禍渦鍋過阪阿際障隙随陪陽陳防附院陣隊墜降階陛隣隔隠堕陥穴空控突究窒窃窟窪搾窯窮探深丘岳兵浜糸織繕縮繁縦緻線綻

// our simple regexp matching needs this so that vocab with okurigana is considered known
const HIRAGANA = 'ぁあぃいぅうぇえぉおかがきぎくぐけげこごさざしじすずせぜそぞただちぢっつづてでとどなにぬねのはばぱひびぴふぶぷへべぺほぼぽまみむめもゃやゅゆょよらりるれろゎわゐゑをんゔゕゖ ゙ ゚゛゜ゝゞゟ'
const KATAKANA = '゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ'
const PUNCTUATION = '｟｠｡｢｣､･ｦｧｨｩｪｫｬｭｮｯｰｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜﾝﾞ'

// cf. rtkLabs.php (this will go into an include)
/*
const PRI_ICHI1 = 0X80
const PRI_NEWS1 = 0X40
const PRI_NEWS2 = 0X20
const PRI_ICHI2 = 0X10
const PRI_SPEC1 = 8
const PRI_SPEC2 = 4
const PRI_GAI1  = 2
const PRI_GAI2  = 1
*/


export default {
  name: 'KoohiiDictList',

  components: {
    cjk_lang_ja
  },

  mixins: [
    KoohiiLoading
  ],

  props: {
    // an array of vocab entries (compound, reading, glossary)
    items: { type: Array, default: function() { return [] } }
  },

  data() {
    return {
      isLoading: true,

      // whether we have already requested them from server
      isSetKnownKanji: false,

      // a string containing all kanji known by the user
      knownKanji: ''
    }
  },

  methods: {
    load(ucsId)
    {
      this.isLoading = true

      function doLoad() {
        this.koohiiloadingShow({ target: this.$refs.maskArea })

        // getKnownKanji:
        // 
        //   We request these only once for the lifetime of the component. This is more
        //   efficient in the flashcard review page.
        //   
        //   The user's known kanji could realistically be 2000 to 3000 utf8 characters. So
        //   even though they are also cached in php session, it's better to avoid returning
        //   several KBs of data with each dictionary lookup request

        KoohiiAPI.getDictListForUCS({
          ucsId: ucsId,
          getKnownKanji: false === this.isSetKnownKanji
        },
        {
          then: this.onDictListResponse.bind(this)
        });
      }

      this.$nextTick(doLoad)
    },

    onDictListResponse(tron)
    {
      const props = tron.getProps()

      // console.log('onDictListResponse(%o)', props)
// return
      this.koohiiloadingHide()

      if (props.known_kanji) {
        this.knownKanji = props.known_kanji
        this.isSetKnownKanji = true
      }

      this.formatItems(props.items)

      this.isLoading = false
    },

    formatItems(items, knownKanji)
    {
      // if (this.known_kanji !== '') {
      //   console.log(' known_kanji : ' + this.known_kanji)
      // }
      const KNOWN_KANJI = this.knownKanji + HIRAGANA + KATAKANA + PUNCTUATION

      // a basic string search could be faster - it's a very small list though
      const regexp = new RegExp('^['+KNOWN_KANJI+']+$')
      items.forEach(item => {
        item.known = regexp.test(item.compound)
      })

      // sort known vocab first
      const knownItems  = items.filter(o => o.known === true)
      const unkownItems = items.filter(o => o.known === false)
      const sortedItems = knownItems.concat(unkownItems)

      this.items = sortedItems
      // this.known_kanji = knownKanji
    }
  },

  created() {
    console.log('KoohiiDictList::created(%o)', this.items);

    this.isLoading = true
  },

  beforeDestroy() {
    console.log('KoohiiDictList::beforeDestroy()');
  }

}
</script>

<style>
/* Dictionary Lookup Component */

.dict-panel { background:#fff; max-height:80vh; overflow-y:auto; }

.dict-list { margin:0; background:#fff; }
.dict-list .cj-k  { line-height:1em; }

.dict-list dt    { padding:1em 15px;  border-top:1px solid #eee; font-weight:normal; }
.dict-list dt .c {
  display:inline-block;
  font-size:22px; padding:5px 8px 3px;
  background:#e7e6e2; color:#000;

}
.dict-list dt .known { background:#e6f2cd; color:#206717; }

.dict-list dt .r { font-size:16px; padding:0; display:inline-block; margin:0 0 0 1em; color:#888; }

.dict-list dd    { padding:0 20px 1em; font:14px/1.3em Arial, sans-serif; color:#444; }

.dict-list dt u  { color:#f00; text-decoration:none; }

/* message when no words are found */
.dict-list_info { padding:1em 20px 1px; color:#838279; background:#fff; }


/* desktop & wider screens */
@media screen and (min-width:701px) {
 
  /* Review page only: fix the width inside the dialog from getting too wide */
  .yui-panel .dict-panel { width:400px; } 

}

</style>
