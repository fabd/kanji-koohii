/**
 * Misc. formatting functions.
 * 
 * METHODS
 * 
 *   koohiiformatReading()    Format the highlighted kanji reading from a DictEntry / VocabPick
 *
 * 
 */

export default {

  methods: {
    /**
     * Surround the kanji reading with <em> tag
     *
     * (cf. data/scripts/dict/dict_gen_cache.php where syntax originates)
     *
     * @param {string} kana   compound's reading
     * @return {string} 
     */
    koohiiformatReading(kana) {
      return kana.replace('(', '<em>').replace(')', '</em>')
    }
  }
}
