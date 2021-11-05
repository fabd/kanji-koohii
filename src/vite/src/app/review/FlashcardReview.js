/**
 * FlashcardReview is a reusable flashcard review component. It handles communication
 * with the server (ajax), caching and prefetching of flashcard data, and the state of
 * the review session (forward, backward (undo), end). Also handles shortcut keys.
 *
 * Presentation logic is handled by the "child" class, through events that are notified
 * by FlashcardReview's event dispatcher.
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
 *       num_prefetch     How many flashcards to fetch ahead
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
 *   endReview()          Ends review: flushes postCache to server, then notifies "onEndReview".
 *   forward()            Advance to next flashcard
 *   backward()           Go back one card (undo), notifies "onFlashcardUndo" event, with the last flashcard answer.
 *
 * Methods to get/put information:
 *
 *   getPosition()        Returns current index into the flashcard items array
 *   getFlashcard()       Returns current uiFlashcard, or null
 *   getFlashcardData()   Returns json data for current flashcard
 *   getItems()           Returns array of flashcard ids, length of array = number of flashcards in session
 *   getPostCount()       Returns count of flashcard answers that need to be posted to server
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
 *                        The items are cleared from the postCache. If not cleared, these items will
 *                        be posted again during the next prefetchs.
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

import { getBodyED, kk_globals_get } from "@app/root-bundle";
import AjaxQueue from "@old/ajaxqueue";
import EventDispatcher from "@old/eventdispatcher";
import VueInstance from "@lib/helpers/vue-instance";

import KoohiiFlashcard from "@/vue/KoohiiFlashcard.vue";

const DEFAULT_PREFETCH = 10;

export default class FlashcardReview {
  /** @type {TReviewOptions} */
  options;

  // flashcard selection as an array of flashcard ids
  /** @type {TUcsId[]} */
  items;

  // review position, from 0 to items.length-1
  position = 0;

  // the next position at which to prefetch new flashcard data
  // is recalculated on server reply, based on number of items server returned
  prefetchPos = 0;

  // Cache of flashcard data.
  // Associative array using flashcard ids for retrieval.
  // Flashcard data is maintained for prefetched items and max_undo items,
  // other cards are deleted when they are beyond the undo range.
  //
  // cacheStart and cacheEnd indicate the range of valid flashcard data
  // in the cache array.
  /** @type {{ [key: number]: TCardData }} */
  cache = {};

  cacheStart = 0;
  cacheEnd = 0;
  cacheFrom = 0;

  // max items to cache for undo
  /** @type {number} */
  max_undo;

  // current undo level (number of steps backward)
  /** @type {number} */
  undoLevel = 0;

  // how many items to preload
  num_prefetch = 0;

  /** @type {EventDispatcher} */
  eventDispatcher;

  // array of answer data for flashcards that is not posted yet
  // the data is freeform, the property id corresponds to a flashcard id
  /** @type {TCardAnswer[]} */
  postCache = [];

  /** @type {AjaxQueue} */
  ajaxQueue;

  /** @type {TVueInstanceRef?} */
  curCard;

  /**
   * Initialize the front end Flashcard Review component.
   *
   * @param {TReviewOptions} options
   */
  constructor(options) {
    console.log("FlashcardReview::init(%o)", options);

    console.assert(options.items && options.items.length, "No flashcard items in this selection.");

    // set options and fix defaults
    this.options = options;
    this.options.max_undo = options.max_undo || 3;
    this.options.num_prefetch = options.num_prefetch || DEFAULT_PREFETCH;
    this.options.put_request = options.put_request === false ? false : true;

    // proxies
    this.items = this.options.items;
    this.max_undo = this.options.max_undo;

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

    // flashcard as a Vue component (wip)
    this.curCard = null;

    this.beginReview();
  }

  beginReview() {
    this.notify("onBeginReview");

    this.cache = {};
    this.cacheStart = 0;
    this.cacheEnd = -1;
    this.cacheFrom = -1;
    this.position = -1;
    this.prefetchPos = 0; // when to prefetch new cards, updated by each ajax response

    //
    this.postCache = [];
    this.undoLevel = 0;

    this.forward();
  }

  /**
   * Add or remove onbeforeunload event to warn user of loosing
   * flashcard answers.
   *
   */
  updateUnloadEvent() {
    if (this.getPostCount()) {
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

    // destroy previous card, so it doesn't show while loading next card (if not prefetched)
    this.destroyCurCard();

    // all cards done?
    if (this.position >= this.items.length) {
      this.endReview();
      return;
    }

    // clear backwards cache
    this.cleanCache();

    this.syncReview();

    if (this.cacheEnd < this.position) {
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

  /**
   * Undo (go backwards)
   *
   * To allow undo we always keep a number of answers in the postCache (max_undo).
   * When syncReview() does a prefetch, only the answers that are before max_undo items
   * backwards are posted. Only at the end of the review are the last answers in the
   * "ungo range" flushed out to the server.
   *
   */
  backward() {
    console.assert(this.undoLevel < this.max_undo, "FlashcardReview::backward() undoLevel >= max_undo");

    if (this.position <= 0) {
      return;
    }

    console.assert(this.cacheStart < this.position, "FlashcardReview::backward() on empty cache");

    this.destroyCurCard();
    this.undoLevel++;

    // go back one step and clear postCache at that position
    this.position--;

    // clear the last flashcard answer from the postCache
    if (this.options.put_request) {
      this.notify("onFlashcardUndo", this.unanswerCard());
    }

    this.cardReady();
  }

  /**
   * This function is called only when the current flashcard
   * data is available in the cache.
   */
  cardReady() {
    // notify BEFORE flashcard is created
    this.notify("onFlashcardCreate");

    // we have a cached item for current position
    var oItem = this.getFlashcardData();

    // (wip, refactor) instance Vue comp
    const propsData = {
      cardData: oItem,
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
    let syncData = {};

    // any cards to fetch ?
    if (this.cacheEnd < this.items.length - 1 && this.position >= this.prefetchPos) {
      //
      if (this.cacheFrom <= this.cacheEnd) {
        var from = this.cacheEnd + 1;
        var to = Math.min(from + this.options.num_prefetch, this.items.length) - 1;
        syncData.get = this.items.slice(from, to + 1);
        this.cacheFrom = from;
      }
    }

    // post answers along with cards prefetching, or do it immediately if flushing
    // the postCache
    if (this.options.put_request && (syncData.get || bFlushData)) {
      // if flush, post all, otherwise don't post all, leave some cards behind to allow client ot re-answer (undo)
      /** @type {TCardAnswer[]} */
      let aPostData;

      let i;
      let numToPost = 0;

      if (bFlushData) {
        aPostData = this.postCache;
      } else {
        numToPost = this.getPostCount() > this.max_undo ? this.getPostCount() - this.max_undo : 0;
        aPostData = [];
        for (i = 0; i < numToPost; i++) {
          aPostData.push(this.postCache[i]);
        }
      }

      if (aPostData.length > 0) {
        //console.log('POSTING %d (%o)', numToPost, aPostData);
        syncData.put = aPostData;
      }

      //simulate a timeout
      //if (bFlushData && !this.foo ) { this.foo=true; syncData.flush = 1; console.log('doing it'); }
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
        argument: bFlushData ? "end" : this.cacheFrom,
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
   * @param {{responseJSON: TReviewSyncResponse}} o    The YUI Connect object (extended by AjaxRequest)
   * @param {number | 'end'} argument        Index value if prefetching, 'end' if completing review
   */
  onAjaxSuccess(o, argument) {
    var i,
      oJson = o.responseJSON;

    console.log("FlashcardReview::onAjaxSuccess(%o)", o);

    if (oJson) {
      // cache cards if any
      if (oJson.get && oJson.get.length > 0) {
        console.assert(argument === this.cacheFrom, "onAjaxSuccess(): this.cacheFrom inconsistency");

        // add cards to cache
        this.cacheEnd = this.cacheFrom + oJson.get.length - 1;

        // next prefetch at based on number of items received
        this.prefetchPos = this.cacheFrom + Math.floor(oJson.get.length / 2) + 1;

        // cache items
        for (i = 0; i < oJson.get.length; i++) {
          this.cacheItem(oJson.get[i]);
        }

        this.notify("onCacheReady");
      }

      // clear answers from cache, that were handled succesfully by the server
      if (oJson.put && oJson.put.length > 0) {
        // clear items from the postCache that were succesfully handled
        //console.log("RESPONSE PUT, CLEAR %o", oJson.put);

        for (i = 0; i < oJson.put.length; i++) {
          var id = oJson.put[i];
          this.removePostData(id);
        }

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

  /**
   * Clear flashcard display data for items behind, to free some
   * resources, we only need as much flashcard data behind as needed
   * for undo.
   *
   */
  cleanCache() {
    while (this.cacheStart < this.position - this.max_undo) {
      var id = this.items[this.cacheStart];
      delete this.cache[id];
      this.cacheStart++;
    }
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
   * @return {Dictionary | null}
   */
  getFlashcardData() {
    var id = this.items[this.position];
    return id ? this.cache[id] : null;
  }

  /**
   * Count numner of items in postCache.
   */
  getPostCount() {
    return this.postCache.length;
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
   * Store answer and any other custom data for the current card,
   * to be posted on subsequent ajax requests.
   *
   * @param {TCardAnswer} cardAnswer
   *
   */
  answerCard(cardAnswer) {
    // console.log('FlashcardReview::answerCard(%o)', cardAnswer);
    this.postCache.push(cardAnswer);

    this.updateUnloadEvent();
  }

  /**
   * Cleans up the answer of current flashcard (when going backwards).
   *
   * Must remove item from postCache otherwise when flushing at end of review,
   * the last undo'ed item(s) would be posted whereas the user did not want to.
   *
   * @return  {TCardAnswer}   Returns flashcard answer data (cf. answerCard()) that is being cleared
   */
  unanswerCard() {
    // console.log('FlashcardReview::unanswerCard()');
    console.assert(this.getPostCount() > 0);

    // pop data from the postcache, don't assume order
    const id = this.items[this.position];

    // never null
    const answer = /** @type {TCardAnswer} */ (this.removePostData(id));

    this.updateUnloadEvent();

    return answer;
  }

  /**
   * Remove one element of the postCache array, by id.
   *
   * @param {TUcsId} id ... flashcard id
   * @return {TCardAnswer?}  Returns the spliced element (flashcard answer data).
   */
  removePostData(id) {
    for (let i = 0; i < this.postCache.length; i++) {
      // watchout with === because returned json can have strings for numbers
      if (this.postCache[i].id === id) {
        const item = this.postCache.splice(i, 1)[0];
        return item;
      }
    }

    // dummy return value, this function should never return an empty
    return null;
  }
}
