/**
 * Misc. formatting functions.
 *
 * METHODS
 *
 *   kkFormatReading()
 *
 *
 */

/**
 * Format the highlighted kanji reading from a DictEntry / VocabPick
 * 
 *   Surrounds the kanji reading with <em> tag.
 *   (cf. data/scripts/dict/dict_gen_cache.php where syntax originates)
 *
 * @param {string} kana   compound's reading
 * @return {string}
 */
export function kkFormatReading(kana) {
  return kana.replace("(", "<em>").replace(")", "</em>");
}
