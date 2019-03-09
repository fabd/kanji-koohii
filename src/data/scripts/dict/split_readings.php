<?php
/**
 * Split JMDICT compounds into individual readings (On, Kun, and Kana).
 *
 * THIS SCRIPT WAS WRITTEN IN 2007!
 *
 * 
 * BACKGROUND
 *
 *   This script uses a recursive algorithm to match each kanji in JMDICT words
 *   to its corresponding part in the full reading (ie. furigana).
 *
 *   The script is by no means 100% "correct". When I wrote it I was well aware
 *   that it won't parse everything in JMDICT and there are many special cases.
 *
 *   The goal was to illustrate On and Kun readings for main use kanji, with
 *   example words that are "priority" entries in JMDICT. The script seems to
 *   handle the majority of the "priority" entries so it is good enough for
 *   that purpose.
 *
 *   The script could have been written in Perl too, but I had no idea how to
 *   handle utf8 back then, and I had this utf8.php library...
 *
 *
 * KNOWN ISSUES
 *
 *   Ateji
 *
 *     The script is NOT aware of 当て字 "kanji used for their meaning,
 *     irrespective of reading". So words like 明日 (あした) will simply be
 *     ignored by the algorith and won't appear in the output.
 *   
 *     http://www16.atpages.jp/kanjikentei/jukujikun.html
 *
 *
 * EXAMPLE OUTPUT
 *
 *   1300840  三助  さんすけ  1.さん.2.すけ
 *
 *   <dictid>  <compound>  <reading>  <split readings>
 *
 *   Split readings consist of 1 or more pairs of (type, reading) separated by '.'
 *
 *   Type is a digit:  0 = misc. Kana,  1 = On,  2 = Kun
 *
 *
 * USAGE
 *
 *   Run from script folder (or root path):
 *
 *   $ php split_readings.php > split_readings.utf8
 *
 *
 * REQUIREMENTS
 *
 *   See data/datafiles/README.md for download instructions
 *
 *     data/datafiles/downloads/kanjidic.utf8
 *     data/datafiles/downloads/jmdict.xml.utf8
 *
 *
 * NOTES
 *
 *   Words that don't split are not in the output
 *
 *     <1584660>  明日  あした, あす
 *
 *   Oddities
 *
 *     仝 has no reading
 * 
 */

  define('ROOT_PATH', realpath(dirname(__FILE__).'/../../..'));

  require_once(ROOT_PATH.'/lib/utf8.php');
  require_once(ROOT_PATH.'/lib/CJK.php');
  
  define('YOMI_SEPARATOR', '.');

  define('KANJIDIC', ROOT_PATH.'/data/datafiles/download/kanjidic.utf8');
  define('JMDICT',   ROOT_PATH.'/data/datafiles/download/jmdict.xml.utf8'); // multi-lingual japanese-english dictionary in xml format


  /**
   * DEBUGGING
   *
   * Set constant to a JMDICT entry id, for example 1299400 for 雑誌 ざっし
   * "magazine". Then output everything to CLI:
   *
   *   $ php split_readings.php
   *
   * Set constant to '' to disable debugging.
   */
  define('DEBUG_ENT_SEQ', '');  // jmdict id


/******************************
  PROGRAM START
*******************************/

  $fd = ParserUtils::openFile(KANJIDIC);

  $lines = 0;
  while (!feof ($fd))
  {
    $buffer = fgets($fd, 4096);
    $lines++;
    if ($buffer[0]=='#' || !strlen($buffer)){
      continue;
    }

    $elems = preg_split('/ +/',$buffer);


    $kanji = array_shift($elems);
    $pron  = array();
    while (count($elems)) {
      $elem = array_shift($elems);
      if ($elem[0] > 'Z') break;
    }
    while ($elem!=NULL && $elem[0]!='{' && $elem[0]!='T') {
      $elem = preg_replace('/^-/','',$elem);
      $elem = preg_replace('/-$/','',$elem);
      $elem = preg_replace('/\..*/','',$elem);
      
      $ua_yomi = utf8::toUnicode($elem);
      $is_onyomi = CJK::isKataUCS($ua_yomi[0]) ? 1 : 0;
      if ($is_onyomi) {
        $elem = utf8::fromUnicode(CJK::toHiraganaUCS($ua_yomi));
      }
      $pron[] = array($elem, $is_onyomi);
      
      $elem = array_shift($elems);
    }
    if (empty($pron)) {
      fwrite (STDERR, "No pronunciations? line::".$lines."\n");
      exit();
    }

    $ua_k = utf8::toUnicode($kanji);
    if (count($ua_k)!=1) {
      fwrite (STDERR, "bug? ".$kanji."~".$lines);
      exit();
    }

    if (!isset($kanjis[$kanji])) {
      $kanjis[$kanji] = $pron;
    //  print "Kanji $kanji pron= $pron\n";
    } else {
      fwrite (STDERR, "OOPS already set ".$kanji."  ::  ".$lines++." first ".$kanjis[$kanji]);
      exit();
    }
  }

  ParserUtils::closeFile($fd);

  fwrite (STDERR, "Count Kanjis ".count($kanjis)."\n");
  fwrite (STDERR, "Total ${lines} lines.\n");


  // Pretend those are kanjis that have readings, so entries can be splitted in jmdict
  $kanjis['０']=array(array('ゼロ',0));
  $kanjis['１']=array(array('いち',0));
  $kanjis['２']=array(array('に',0));
  $kanjis['３']=array(array('さん',0));
  $kanjis['４']=array(array('よん',0), array('し',0));
  $kanjis['５']=array(array('ご',0));
  $kanjis['６']=array(array('ろく',0));
  $kanjis['７']=array(array('しち',0), array('なな',0));
  $kanjis['８']=array(array('はち',0));
  $kanjis['９']=array(array('きゅう',0));

  $kanjis['Ａ']=array(array('エー',0), array('ええ',0));
  $kanjis['Ｂ']=array(array('ビー',0), array('びい',0));
  $kanjis['Ｃ']=array(array('シー',0), array('しい',0));  // FIXME parse in hiragana or katakana, but not both
  $kanjis['Ｄ']=array(array('ディー',0));
  $kanjis['Ｅ']=array(array('イー',0));
  $kanjis['Ｆ']=array(array('エフ',0));
  $kanjis['Ｇ']=array(array('ジー',0));
  $kanjis['Ｈ']=array(array('エッチ',0));
  $kanjis['Ｉ']=array(array('アイ',0));
  $kanjis['Ｊ']=array(array('ジェー',0));
  $kanjis['Ｋ']=array(array('ケー',0));     // could add more readings like for 'KGB'... but bleh.
  $kanjis['Ｌ']=array(array('エル',0));
  $kanjis['Ｍ']=array(array('エム',0));
  $kanjis['Ｎ']=array(array('エヌ',0));
  $kanjis['Ｏ']=array(array('オー',0));
  $kanjis['Ｐ']=array(array('ピー',0));
  $kanjis['Ｑ']=array(array('キュー',0));
  $kanjis['Ｒ']=array(array('アール',0));
  $kanjis['Ｓ']=array(array('エス',0));
  $kanjis['Ｔ']=array(array('ティー',0));
  $kanjis['Ｕ']=array(array('ユー',0));
  $kanjis['Ｖ']=array(array('ブイ',0));
  $kanjis['Ｗ']=array(array('ダブリュー',0));
  $kanjis['Ｘ']=array(array('エックス',0));
  //$kanjis['Ｙ']=array(array('ワイ',0));
  //$kanjis['Ｚ']=array(array('',0));


function harden($kana,&$conv)
{
  return preg_replace(array_keys($conv), array_values($conv), $kana);
}

$kana_ka_to_ga = array(
'/^か/' => 'が',
'/^き/' => 'ぎ',
'/^く/' => 'ぐ',
'/^け/' => 'げ',
'/^こ/' => 'ご');

$kana_ha_to_ba = array(
'/^は/' => 'ば',
'/^ひ/' => 'び',
'/^ふ/' => 'ぶ',
'/^へ/' => 'べ',
'/^ほ/' => 'ぼ');

$kana_ha_to_pa = array(
'/^は/' => 'ぱ',
'/^ひ/' => 'ぴ',
'/^ふ/' => 'ぷ',
'/^へ/' => 'ぺ',
'/^ほ/' => 'ぽ');

$kana_ta_to_da = array(
'/^た/' => 'だ',
'/^ち/' => 'ぢ',
'/^つ/' => 'づ',
'/^て/' => 'で',
'/^と/' => 'ど');


$kana_sa_to_za = array(
'/^さ/' => 'ざ',
'/^し/' => 'じ',
'/^す/' => 'ず',
'/^せ/' => 'ぜ',
'/^そ/' => 'ぞ');

$kana_tsu_to_little_tsu = array(
'/つ$/' => 'っ');

$kana_ku_to_little_tsu = array(
'/く$/' => 'っ');



function fdebug($s)
{
  global $debug_ent_seq;
  static $first=true;
  if ($debug_ent_seq==DEBUG_ENT_SEQ) {
    if ($first) {
      $first=false;
      print "\n\n~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DEBUG ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n\n";
    }
    print $s;
  }
}
function fdebugprint_r($s)
{
  global $debug_ent_seq;
  if ($debug_ent_seq==DEBUG_ENT_SEQ) {
    print_r($s);
  }
}


function splitkanjipron($word, $pron, $lastkanji='')
{
  global $kanjis;
  
fdebug("splitkanjipron($word , $pron , $lastkanji)...\n");
  $ua_word = utf8::toUnicode($word);
  $u_k = array_shift($ua_word);
  if ($u_k==NULL) {
    // if already parsed all word AND pron, then we're done
    if ($pron=='') {
      return '';
    }
    // mismatch (more kana than kanji left)
    return false;
  }
  if ($pron=='') {
  //  print "  ! empty pron, kanji left: $word\n";
    return false;
  }

  $temp = array($u_k);
  $k = utf8::fromUnicode($temp);
  $word = utf8::fromUnicode($ua_word);
  
  if ($u_k == 0x3005) /* 々 */
  {
//    print "... repetition symbol\n";
    if ($lastkanji!='') {
      return separate_kanji_pron_try($word, $pron, $lastkanji);
    }
    // compound should not start with 々
    return false;
  }
  elseif (isset($kanjis[$k]))
  {
    return separate_kanji_pron_try($word, $pron, $k);
  }
  elseif ($u_k < 0x3100 || $u_k >=0xff00)
  {
    // doesn't look like a kanji
    // KATAKANA, special char, full-width roman char, half-width kana, ...

    // it's a kana, convert KATAKANA because the compound reading is always hiragana
    /*
    if (CJK::isKataUCS($u_k)) {
      $u_k -= 0x0060; // shift to hiragana ucs
      $ua = array($u_k);
      $k = utf8::fromUnicode($ua);      
    }
    */
    
    if (strpos($pron, $k)===0) {
//      print "... FOUND KANA '${k}'\n";
      $pron = substr($pron, strlen($k));
      if (($next = splitkanjipron($word, $pron, $k))!==false)
      {
        fdebug (" -> kana recurseOK -> $k\n");
        return '0'.YOMI_SEPARATOR.$k.YOMI_SEPARATOR.$next;
      }
      return false;
    }
    return false;
  }
  else
  {
    fwrite (STDERR, "... no reading for $k (unicode $u_k)\n");
    /* ucs 20189, 21581 */
    return false;
  }
  
  return false;
}

function separate_kanji_pron_try($word, &$pron, $k)
{
  global $kanjis;
  global $kana_ka_to_ga, $kana_ha_to_ba, $kana_ha_to_pa, $kana_ta_to_da, $kana_sa_to_za, $kana_tsu_to_little_tsu, $kana_ku_to_little_tsu;
  
fdebug("  separate_kanji_pron_try($word , $pron <<< $k)...\n");

  $readings = $kanjis[$k];
  
  $among = array();
  $aorig = array(); // the original reading, not hardened
  foreach ($readings as $yomi) {
    $R = $yomi[0];
    
    // there can be multiple identical readings like '-ta' or 'ta-'
    // but also a kunyomi identical to onyomi, in which case we want to match the onyomi in word formation
    // the onyomi comes first, so if set, skip the rest
    if (!isset($among[$R]))
    {
      $among[$R] = $yomi[1];
      $aorig[$R] = $R;
    }
    
    $hardR = harden($R,$kana_ka_to_ga); if (!isset($among[$hardR])) { $among[$hardR] = $yomi[1]; $aorig[$hardR]=$R; }
    $hardR = harden($R,$kana_ha_to_pa); if (!isset($among[$hardR])) { $among[$hardR] = $yomi[1]; $aorig[$hardR]=$R; }
    $hardR = harden($R,$kana_ta_to_da); if (!isset($among[$hardR])) { $among[$hardR] = $yomi[1]; $aorig[$hardR]=$R; }
    $hardR = harden($R,$kana_sa_to_za); if (!isset($among[$hardR])) { $among[$hardR] = $yomi[1]; $aorig[$hardR]=$R; }
    $hardR = harden($R,$kana_ha_to_ba); if (!isset($among[$hardR])) { $among[$hardR] = $yomi[1]; $aorig[$hardR]=$R; }
    $hardR = harden($R,$kana_tsu_to_little_tsu); if (!isset($among[$hardR])) { $among[$hardR] = $yomi[1]; $aorig[$hardR]=$R; }
    $hardR = harden($R,$kana_ku_to_little_tsu); if (!isset($among[$hardR])) { $among[$hardR] = $yomi[1]; $aorig[$hardR]=$R; }
  }
  
//  print "  ~~ ".count($readings)." / ".count($among)." variations...\n";
  fdebugprint_r($among);

$multiples = 0;
$success = '';

$c=1;
  foreach ($among as $R => $rtype)
  {
//print "\n  ___foreach step $c reading '$R' ... ";$c++;

    $ua_yomi = utf8::toUnicode($R);

    if (strpos($pron, $R)===0)
    {
      fdebug(" FOUND ".($rtype?'On ':'Kun')." reading ${R} in '${pron}'\n");
      // try ahead
      $pron2 = substr($pron, strlen($R));
      if (($next = splitkanjipron($word, $pron2, $k))!==false)
      {
        fdebug(" -> recurseOK -> $R\n");

        $multiples++;
        if ($multiples>1) {
          fwrite(STDERR, "MULTIPLE matches (previous = '".$success."'\n");
        }

      //  $pron = substr($pron, strlen($R));
        // 1=On 2=Kun (0=Kana)
       
        // (fabd 2017/08)  here $aorig[$R] would be matched pronunciation (non hardened)

        $success = ((1-$rtype)+1).YOMI_SEPARATOR.$R.YOMI_SEPARATOR.$next;
        break;
      }
    //  print "  TryAhead Failed for ".$R." in '$pron'\n";
    }
  }

  if ($success!='') {
    return $success;  
  }
  
  fdebug("  ___failed for '$k' pron '$pron'\n");
  return false;
}


/*
  $testword = '沢山'; //'友達';
  $testpron = 'ともだち';
  $a = splitkanjipron($testword, $testpron);
*/
//print_r($kanjis['達'])."\n\n";

function parse_test()
{
  $filename = $argc>1 ? $argv[1] :  'compounds.utf8';

  $fd = ParserUtils::openFile($filename);

  $lines = 0;
  while (!feof ($fd)) {
      $buffer = fgets($fd, 4096);
      $lines++;
    if ($buffer[0]=='#' || !strlen($buffer)){
      continue;
    }
    $chomp = preg_replace('/[\r\n]/','',$buffer);
    if (empty($chomp))
      continue;
    
    $a = preg_split('/\t/',$chomp);
    if (count($a)==2) {
      
      $splitted = splitkanjipron($a[0], $a[1]);
      print "${a[0]} (${a[1]}) : ".($splitted ? $splitted : '-FAILED-')."\n";
    }
    else {
      print "ERR: Bad line $lines >> ".$buffer."\n";
    }
  }

  ParserUtils::closeFile($fd);
}

function parse_jmdict()
{
  global $debug_ent_seq;

  $fd = ParserUtils::openFile(JMDICT);

  $lines = 0;
  $numentries = 0;
  $skipped = 0;

  // parsing kanjidic2 in xml format:
  
  while (!feof ($fd))
  {
    $buffer = fgets($fd, 4096);
    $lines++;

    if (!strncmp('<ent_',$buffer,5))
    {
      if(preg_match('@^<ent_seq>(\d+)</ent_seq>@',$buffer,$matches))
      {
        $numentries++;
        $debug_ent_seq = $matches[1];//for debugging particular entries of the dic.
        if ($numentries%1000==1) {
          fwrite(STDERR,'% entry '.$numentries.' at line '.$lines." ($skipped skipped)\n");
        }

        // new glossary entry
        $eEntSeq = $matches[1];
        $eCurReb = NULL;  //current reading(reb) from r_ele
        $eKebs = array();  //associative array of possible compounds for entry
        $eRestr = array();   //compounds(k_ele) to which current reading(r_ele) is restricted, if empty reading applies to all compounds (kebs)
        $eNumReb = 0;
        $eNumKeb = 0;
      }
      else {
        fwrite(STDERR,"oops lines $lines\n");
      }
    }
    elseif (preg_match('/<keb>([^<]+)/',$buffer,$matches))
    {
      // each compound will have array of associated readings
      $eKebs[$matches[1]] = array();
      $eNumKeb++;
    }
    elseif (!strncmp('<r_ele',$buffer,6))
    {
      $eRestr = array();
      $eNumReb++;
    }
    elseif (preg_match('/<reb>([^<]+)/',$buffer,$matches))
    {
      $eCurReb = $matches[1];
    }
//  elseif (preg_match('/^<ke_pri>/',$buffer))
//  {
//    $E->ke_pri=true;
//  }
    elseif (preg_match('/<re_restr>([^<]+)/',$buffer,$matches))
    {
      // current reading applies only to restricted compounds
      $eRestr[] = $matches[1];
    }
    elseif (!strncmp('</r_ele',$buffer,7))
    {
      if (count($eRestr))
      {
        // apply reading to a subset of the compounds
        foreach ($eRestr as $k_ele) {
          if (!isset($eKebs[$k_ele])){
            # dict file error : <re_restr> points to unspecified compound (probably a typo)
            fwrite(STDERR,"WARNING #1311 in ent_seq#".$eEntSeq."\n");
            continue;
          }else{
            $eKebs[$k_ele][] = $eCurReb;
          }
        }
      }
      else if (count($eKebs))
      {
        // reading applies to all given compound in this entry
        foreach ($eKebs as $k => $v){
          $eKebs[$k][] = $eCurReb;
        }
      }
    }
    elseif (!strncmp('</entry',$buffer,7))
    {

      //print "go go split ".$E->keb." [".$E->reb."] \n";
      //if (is_null($E->ke_pri)) {
      //  print "Missing ke_pri: ".$E->keb." [".$E->reb."] (ent_seq ".$E->ent_seq.")\n";
      //}

      // help debugging - speedup getting to the entry to debug
      if (DEBUG_ENT_SEQ !== '' && $eEntSeq !== DEBUG_ENT_SEQ) { continue; }

      foreach ($eKebs as $k_ele => $v)
      {
        // jmdict error : one compound in entry missing reading(s) due to <re_restr>
        if (!count($eKebs[$k_ele])) {
          fwrite(STDERR,"MISSING readings in ent_seq#".$eEntSeq."\n");
        }
        
        $rebs = $eKebs[$k_ele];
        $splitdone = array();
        foreach ($rebs as $r_ele)
        {
          // unique compound > reading association
          
          if (($splitted = splitkanjipron($k_ele, $r_ele))!==false)
          {
          //  print "OK `".$E->keb."` [ ".$E->reb." ] (ent_seq ".$E->ent_seq.") { ".$splitted." }\n";
            $splitted = substr($splitted,0,-1); //chop last separator char.

            print $eEntSeq."\t".$k_ele."\t".$r_ele."\t".$splitted."\n";
          }
          else
          {
          //  print "Could not split `".$E->keb."` [ ".$E->reb." ] (ent_seq ".$E->ent_seq.")\n";
            //exit();
          }
        }
      }

      // help debugging
      if (DEBUG_ENT_SEQ !== '' && DEBUG_ENT_SEQ === $eEntSeq) { exit(); }
    }
  }

  ParserUtils::closeFile($fd);

  fwrite(STDERR,$numentries." parsed entries\n");
}


class ParserUtils
{
  public static function openFile($fileName, $mode = "r")
  {
    try {
      $handle = fopen($fileName, $mode);
    }
    catch (coreException $e) {
      echo sprintf('Error opening file: "%s" with mode "%s"', $fileName, $mode);
    }

    return $handle;
  }

  public static function closeFile($fileHandle)
  {
    fclose($fileHandle);
  }
}

  parse_jmdict();

