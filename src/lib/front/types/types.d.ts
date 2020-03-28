export interface KanjiReview {
  oReview: any;
  toggleDictDialog: () => void;
}

////////////////////////////////////////////////////////////
// Kanji Review / Dictionary popup
////////////////////////////////////////////////////////////

export type DictId = number;

/**
 * DictListEntry
 * 
 */
export interface DictListEntry {
  id: DictId; // jdict.id
  c: string; // compound
  /** r: reading */
  r: string; // reading
  /**
   * Stuff:
   * - qsdqdqsd qsdqsd its really **strong** what _u think_ qsdsq
   * - but alos `qsdqsdq` <strong>strongk</strong> its *haha*
   */
  g: string; // glossary
  pri: number; // jdict.pri (bitfield)

  // FIXME? refactor to use a separate hash for DictList templating
  known?: boolean; // (client side) true if user knows all kanji in this compound
  fr?: string; // formatted reading
  pick?: boolean; // selected state
}
