// --------------------------------------------------------------------
// Abstract types & utilities
// --------------------------------------------------------------------
type Dictionary<T = any> = { [key: string]: T };

// --------------------------------------------------------------------
// Basic data types
// --------------------------------------------------------------------
// a kanji as a UCS code ( note! database currently stores as UCS-2 )
type TUcsId = number;

// a kanji as a single character string (utf)
type TKanjiChar = string;

// --------------------------------------------------------------------
// Dictionary Lookup
// --------------------------------------------------------------------
type DictId = number;

/**
 * A subset of a JMDICT entry as obtained from dict lookup cache.
 *
 * @see data/scripts/dict/dict_gen_cache.php
 */
type DictListEntry = {
  id: DictId; // jdict.id
  c: string; // compound
  r: string; // reading
  g: string; // glossary
  pri: number; // jdict.pri (bitfield)
};

/**
 * This could be more complex later, like additional metadata about the results
 * for a given UCS code.
 */
type DictResults = DictListEntry[];
