/**
 * FlashcardReview handles
 *
 * - caching and prefetching of flashcard data
 * - syncing answers with the server (using AjaxQueue)
 * - the state of the review session (forward, backward & undo, end)
 *
 * For SRS/stateful review modes:
 *
 *   TO ADVANCE
 *     answerCard(answer)             to rate a card
 *     forward()
 *
 *   TO GO BACK
 *     undo()                         to undo answer & go backwards
 *
 *
 * For free reviews where undo is not needed:
 *
 *   TO ADVANCE
 *     forward()
 *
 *   TO GO BACK
 *     backward()
 *
 *
 * Public methods:
 *
 *   initialize(options)   Constructor, pass an options object:
 *
 *       items            An array of flashcard ids (REQUIRED)
 *       ajax_url         Url of ajax action to get/post flashcard data (REQUIRED)
 *       back_url         Http location to go to when review ends with no reviews
 *
 *       params           Parameters sent with every request (maintains state of review options) (OPTIONAL)
 *
 *       max_undo         Maximum undo/backward level
 *       events           An object with events to register (see notifications below)
 *       scope            Scope to use for the events (property, optional)
 *       put_request      Set to false if not posting any answers to the server. "onEndReview" will be
 *                        notified automatically after forward() has moved past the last item.
 *
 *   connect(sName, fnEvent [,scope])   Add a listener
 *   disconnect(sName)                  Remove a listener
 *   notify(sName[, args...])           Notify listeners
 *
 *
 * Methods to control the review session:
 *
 *   beginReview()        Called automatically when FlashcardReview is instanced.
 *
 *   forward()            Advance to next flashcard.
 *   backward()           Go back to previous card.
 *
 *   endReview()          Ends review: flushes postCache to server, then notifies
 *                         "onEndReview".
 *
 *   answerCard(answer)   Rate the current card, add any optional data to be synced
 *
 *   undo()               Undo the last card answer, then go backward().
 *                        Notifies "onFlashcardUndo" event, with the undo-ed answer.
 *
 *
 * Properties
 *
 *   numRated
 *   numAgain
 *
 *
 * Methods to get/put information:
 *
 *   getPosition()        Returns current index into the flashcard items array
 *   getFlashcard()       Returns current uiFlashcard, or null
 *   getFlashcardData()   Returns json data for current flashcard
 *   getItems()           Returns array of flashcard ids, length of array = number of flashcards in session
 *   getNumUndos()        Returns number of undo levels currently available
 *   getFlashcardState()  Returns the current state (0 = default), or false if no current card is set
 *   setFlashcardState(n) Sets state for current flashcard, this changes the "uiFcState" class name on the flashcard
 *   answerCard(oData)    Set data to post back to server as answer for current card.
 *                        This is not required if option "put_request" is false.
 *
 * Notifications:
 *
 *   onBeginReview        When review begins, during FlashcardReview initialization
 *   onEndReview          Notified when the last flashcard is answered and the post cache has been successfully handled by server
 *   onFlashcardCreate    Before new flashcard is created and shown. Flashcard data is available and can be changed
 *                        with getFlashcardData()
 *   onFlashcardState(n)  Called just after 'onFlashcardCreate' for default state 0, and then everytime the state
 *                         is set with setFlashcardState(). Argument n is the state number.
 *   onFlashcardDestroy   Before the current flashcard is destroyed
 *   onFlashcardUndo(o)   Called when user undo'es flashcard answer. Argument o is the answer data as it was passed
 *                        to answerCard(). Notified only if "put_request" is true (default).
 *
 * Ajax RESPONSE format (in Json):
 *
 *   get                  Array of flashcard data, one object for each id that came in request.
 *                        Properties that match elements of class "fcData-xxx" where xxx is the property name,
 *                        are automatically loaded into the element (innerHTML, html allowed), other properties
 *                        may be used by the peer class.
 *                        Returned objects must have the property "id" set to the corresponding flashcard id's.
 *
 *   put                  Array of flashcard ids which were succesfully handled.
 *
 *
 * Usage:
 *
 *   #uiFcAjaxLoading
 *     => div with "Loading..." message shown during Ajax request.
 *   #uiFcAjaxError
 *     => div that shows for ajax errors, or server errors, span.msg is set to error message.
 *   DIV.uiFcCard
 *     => the flashcard container
 *   .uiFcState-N
 *     => the flashcard state, where N is a number, 0 is the default state, use with css rules to set
 *        visibility of various information on the card depending on state (eg. 0 = question, 1 = answer)
 *
 *
 */
// @ts-check

import { kk_globals_get } from "@app/root-bundle";
import AjaxQueue from "@old/ajaxqueue";
import EventDispatcher from "@old/eventdispatcher";
import VueInstance from "@lib/helpers/vue-instance";

import KoohiiFlashcard from "@/vue/KoohiiFlashcard.vue";

const PREFETCH_CARDS = 10;

export const FCRATE = {
  NO: "no",
  AGAIN: "again",
  AGAIN_HARD: "again-hard",
  AGAIN_YES: "again-yes",
  AGAIN_EASY: "again-easy",
  YES: "yes",
  EASY: "easy",
  DELETE: "delete",
  SKIP: "skip",
  HARD: "hard",
};

export default class FlashcardReview {
  /** @type {TReviewOptions} */
  options;

  // flashcard selection as an array of flashcard ids
  /** @type {TUcsId[]} */
  items;

  // handle unique items vs repeat items
  numCards = 0;
  // how many cards are rated (not counting "again" answers)
  numRated = 0;
  /** @type {Map<number, true>} */
  againCards;

  // review position, from 0 to items.length-1
  position = 0;

  /**
   * Cache of flashcard data. The key is the kanji (UCS code).
   *
   * @type {{ [key: number]: TCardData }}
   */
  cache = {};

  // cacheEnd indicate the range of valid flashcard data in the cache array.
  cacheEnd = 0;

  isAwaitingCards = false;

  /**
   * Flashcard answers in the same order than items[].
   *
   * @type {TCardAnswer[]}
   */
  postCache = [];

  //
  postCacheFrom = 0;

  // max items to cache for undo
  /** @type {number} */
  max_undo;

  // current undo level (number of steps backward)
  /** @type {number} */
  undoLevel = 0;

  /** @type {EventDispatcher} */
  eventDispatcher;

  /** @type {AjaxQueue} */
  ajaxQueue;

  /** @type {TVueInstanceRef?} */
  curCard = null;

  /**
   * Initialize the front end Flashcard Review component.
   *
   * @param {TReviewOptions} options
   */
  constructor(options) {
    console.log("FlashcardReview::init(%o)", options);

    console.assert(options.items && options.items.length, "No flashcard items in this selection.");

    // set options and fix defaults
    options.put_request = options.put_request === false ? false : true;
    this.options = options;

    this.max_undo = options.max_undo || 3;

    //
    this.items = options.items;
    this.numCards = options.items.length;
    this.numRated = 0;
    this.againCards = new Map();

    // register listeners
    this.eventDispatcher = new EventDispatcher();
    const scope = options.scope;
    for (var sEvent in options.events) {
      this.eventDispatcher.connect(sEvent, options.events[sEvent], scope);
    }

    // init ajax
    this.ajaxQueue = new AjaxQueue({
      elError: "uiFcAjaxError",
      elLoading: "uiFcAjaxLoading",
      events: {
        onSuccess: this.onAjaxSuccess.bind(this),
      },
    });

    this.beginReview();
  }

  /** @readonly */
  get numAgain() {
    return this.againCards.size;
  }

  /**
   * Returns true if all cards in the initial items[] array are cached
   */
  get isCacheFull() {
    return this.cacheEnd >= this.numCards;
  }

  beginReview() {
    this.notify("onBeginReview");

    this.cache = {};
    this.cacheEnd = 0;
    this.position = -1;

    this.postCache = [];
    this.postCacheFrom = 0;

    this.undoLevel = 0;

    this.forward();
  }

  /**
   * Check if there are answers, and if they are not all yet synced to server.
   *
   */
  isPostCacheDirty() {
    return this.postCache.length && this.postCacheFrom < this.position;
  }

  /**
   * Add or remove onbeforeunload event to warn user of loosing
   * flashcard answers.
   *
   */
  updateUnloadEvent() {
    // debug
    // console.table(this.postCache.map((a) => ({ id: a.id, k: String.fromCharCode(a.id), r: a.r })));
    // console.log("ITEMS ", this.items.join("-"));
    // console.log("AGAIN ", [...this.againCards.keys()].join());
    if (this.isPostCacheDirty()) {
      window.onbeforeunload = function () {
        return (
          "WAIT! You may lose a few flashcard answers if you leave the page now.\r\n" +
          "Select CANCEL to stay on this page, and then click the END button to\r\n" +
          "complete this review session."
        );
      };
    } else {
      window.onbeforeunload = null;
    }
  }

  /**
   * EventDispatcher proxy.
   *
   * @param {string}    name     The type of event (the event's name)
   * @param {Function}  fn       A javascript callable
   * @param {Object=}    context  Context (this) for the event. Default value: the window object.
   */
  connect(name, fn, context) {
    this.eventDispatcher.connect(name, fn, context);
  }

  /**
   * EventDispatcher proxy.
   *
   * @param {string}    name   An event name
   * @param {Function=}  fn     A javascript callable (optional)
   */
  disconnect(name, fn) {
    this.eventDispatcher.disconnect(name, fn);
  }

  /**
   * EventDispatcher proxy.
   *
   * @param {string} name
   * @param {...*} params
   */
  notify(name, ...params) {
    return this.eventDispatcher.notify(name, ...params);
  }

  /**
   * Go back to previous page if no cards were answered yet,
   * otherwise flush post cache and notify review end.
   *
   */
  endReview() {
    if (this.position <= 0) {
      // redirect to back_url
      if (this.options.back_url) {
        window.location.href = this.options.back_url;
        return;
      } else {
        return;
      }
    }

    // clear last card from display
    this.destroyCurCard();

    if (this.options.put_request) {
      // flush post cache and will notify end of review on ajax response
      this.syncReview(true);
    } else {
      // notify end of review here because there is nothing to post
      this.notify("onEndReview");
    }
  }

  forward() {
    this.position++;

    if (this.undoLevel > 0) {
      this.undoLevel--;
    }

    this.updateUnloadEvent();

    // destroy previous card, so it doesn't show while loading next card (if not prefetched)
    this.destroyCurCard();

    // all cards done?
    if (this.position >= this.items.length) {
      this.endReview();
      return;
    }

    this.syncReview();

    // if the cache is not full yet...
    if (this.position >= this.cacheEnd && !this.isCacheFull) {
      // this happens normally only on review start, when cache is empty
      // OR review catches up with the card pre-fetch (server is very slow to respond)
      this.connect("onCacheReady", () => {
        this.disconnect("onCacheReady");
        this.cardReady();
      });
    } else {
      // if card is already prefetched, handle it!
      this.cardReady();
    }
  }

  backward() {
    if (this.position <= 0) {
      return;
    }

    this.destroyCurCard();

    // go back one step and clear postCache at that position
    this.position--;

    this.cardReady();
  }

  /**
   * Undo : unanswer the last card, and go backwards.
   *
   * To allow undo we always keep a number of answers in the postCache (max_undo).
   * When syncReview() does a prefetch, only the answers that are before max_undo items
   * backwards are posted. Only at the end of the review are the last answers in the
   * "ungo range" flushed out to the server.
   */
  undo() {
    console.assert(this.undoLevel < this.max_undo, "FlashcardReview::backward() undoLevel >= max_undo");
    if (this.undoLevel >= this.max_undo) {
      return;
    }

    this.undoLevel++;

    this.backward();
    this.notify("onFlashcardUndo", this.unanswerCard());
  }

  /**
   * This function is called only when the current flashcard
   * data is available in the cache.
   *
   * FIXME : this code should be handled in review-kanji/vocab
   */
  cardReady() {
    // notify BEFORE flashcard is created
    this.notify("onFlashcardCreate");

    // we have a cached item for current position
    const cardData = /** @type TCardData */ (this.getFlashcardData());

    //
    cardData.isAgain = this.position >= this.numCards;

    // (wip, refactor) instance Vue comp
    const propsData = {
      cardData: cardData,
      reviewMode: kk_globals_get("REVIEW_MODE"),
    };

    this.curCard = VueInstance(KoohiiFlashcard, "#uiFcMain", propsData);

    // notifies 'onFlashcardState'
    this.setFlashcardState(0);

    this.curCard.vm.display(true);
  }

  /**
   * Clears current flashcard, so that it disappears
   * until the next one is ready.
   */
  destroyCurCard() {
    if (this.curCard) {
      this.notify("onFlashcardDestroy");

      this.curCard.unmount();
      this.curCard = null;
    }
  }

  /**
   * Check if there are cards to prefetch, and/or answers to post.
   *
   * @param {boolean=} bFlushData ... At end of review, force flush all remaining items in postCache.
   */
  syncReview(bFlushData) {
    /** @type {TReviewSyncRequest} */
    const syncData = {};

    const syncNow =
      // start of review
      this.position === 0 ||
      // ... or every N cards
      this.position % PREFETCH_CARDS === Math.floor(PREFETCH_CARDS / 2);

    // FETCH CARDS
    if (syncNow && !this.isCacheFull) {
      //
      if (!this.isAwaitingCards) {
        const from = this.cacheEnd;
        const to = Math.min(from + PREFETCH_CARDS, this.numCards);
        syncData.get = this.items.slice(from, to);
        this.isAwaitingCards = true;
      }
    }

    // POST ANSWERS
    // - post all remaining answers if flushing the postCache at end of review
    // - always keep some answers to allow for undo levels
    if ((syncNow || bFlushData) && this.options.put_request) {
      // if flush, post all, otherwise post in small batches, and leave
      //  some cards behind for the under feature
      const syncEnd = bFlushData ? this.position : Math.max(0, this.position - this.max_undo);

      const aPostData = this.postCache.slice(this.postCacheFrom, syncEnd);
      this.postCacheFrom = syncEnd;

      if (aPostData.length > 0) {
        syncData.put = aPostData;
      }
    }

    console.log("FlashcardReview::syncReview(%o)...", syncData);

    if (syncData.get || syncData.put) {
      if (syncData.get && this.options.params) {
        // pass the flashcard options, if provided
        syncData.opt = this.options.params;
      }

      this.ajaxQueue.add(this.options.ajax_url, {
        method: "post",
        json: syncData,
        argument: bFlushData ? "end" : "continue",
      });
      this.ajaxQueue.start();

      return true;
    }

    return false;
  }

  /**
   * AjaxQueue success callback (HTTP 200 only).
   *
   * Cache items returned by the server,
   * determine next position to start prefetch based on how many items were received.
   *
   * @param {{responseJSON: TReviewSyncResponse}} o ... The YUI Connect object (extended by AjaxRequest)
   * @param {number | 'end'} argument ... 'end' if completing review
   */
  onAjaxSuccess(o, argument) {
    const syncResponse = o.responseJSON;

    console.log("FlashcardReview::onAjaxSuccess(%o)", o);

    if (syncResponse) {
      const cardsData = syncResponse.get;

      // cache cards if any
      if (cardsData && cardsData.length) {
        // increase pointer to last cached card data
        this.cacheEnd += cardsData.length;

        // cache items
        cardsData.forEach((item) => this.cacheItem(item));

        this.isAwaitingCards = false;

        this.notify("onCacheReady");
      }

      // clear answers from cache, that were handled succesfully by the server
      if (syncResponse.put && syncResponse.put.length > 0) {
        this.updateUnloadEvent();
      }

      // completing review
      if (argument === "end") {
        this.notify("onEndReview");
      }
    }
  }

  /** @param {TCardData} cardData */
  cacheItem(cardData) {
    this.cache[cardData.id] = cardData;
  }

  getPosition() {
    return this.position;
  }

  /** @return {TVueInstanceOf<typeof KoohiiFlashcard>?} */
  getFlashcard() {
    return (this.curCard && this.curCard.vm) || null;
  }

  /**
   * The returned object needs to be cast based on the given Flashcard review mode.
   *
   * @return {TCardData | null}
   */
  getFlashcardData() {
    var id = this.items[this.position];
    return id ? this.cache[id] : null;
  }

  getNumUndos() {
    return Math.min(this.position, this.max_undo - this.undoLevel);
  }

  getItems() {
    return this.items;
  }

  /** @param {number} state */
  setFlashcardState(state) {
    this.curCard && this.curCard.vm.setState(state);
    this.notify("onFlashcardState", state);
  }

  getFlashcardState() {
    return this.curCard ? this.curCard.vm.getState() : false;
  }

  /**
   * Keep track of answers, to be synced later with server.
   *
   * - update counts of rated & again cards
   * - append answer at end of postCache[]
   * - append a copy of the card at end of items[], if using "again"
   *
   * @param {TCardAnswer} answer
   */
  answerCard(answer) {
    // keep track of repeat cards
    if (answer.r === FCRATE.AGAIN) {
      // add a copy of this card to the end of the review pile
      //  (progress bar won't move after forward() since the length just increased)
      this.items.push(answer.id);
      this.againCards.set(answer.id, true);
    } else {
      this.numRated++;

      // if the card was last rated "again", remove it from the "again" count
      this.againCards.delete(answer.id);
    }

    this.postCache[this.position] = answer;
  }

  /**
   * Update the state of the review when undo-ing an answer.
   *
   * - update counts of rated & again cards
   * - remove the answer from postCache[]
   * - remove duplicate cards from the end of items[], added previously by "again"
   *
   * @return {TCardAnswer} Flashcard answer (cf. answerCard()) that is "undone"
   */
  unanswerCard() {
    console.assert(this.postCache.length);

    const answer = /** @type {TCardAnswer}*/ (this.postCache.pop());

    // if it was a "again" card, remove its duplicata from the end of items[]
    if (answer.r === FCRATE.AGAIN) {
      this.items.pop();
    }

    // keep count of rated (ie. not "again") cards
    if (!this.againCards.has(answer.id)) {
      this.numRated--;
    }

    // whether we are seeing the card again, in the same review session
    const isRepeatCard = this.position >= this.numCards;

    // if undo-ing an "again >> hard/yes/easy/etc" card, re-count it as "again" answer
    if (isRepeatCard) {
      this.againCards.set(answer.id, true);
    } else {
      // a non-repeat card, always clear from "again" count
      this.againCards.delete(answer.id);
    }

    this.updateUnloadEvent();

    return answer;
  }
}
