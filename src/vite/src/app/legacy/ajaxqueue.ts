/**
 * AjaxQueue handles single and mutliple ajax requests in sequential fashion.
 *
 * Simply queue() urls and options as for uiAjaxRequest, and the callbacks will
 * be called in sequence.
 *
 * Additionally:
 *
 * - Timeout and errors will display an error dialog. Clicking reconnect will
 *   resend the last request. The success notification is only fired when the request
 *   succesfully through.
 *
 * - Server-side ajax exceptions can set the error message through the 'RTK-Error'
 *   HTTP Header. It will show in the connection error dialog.
 *
 * Methods:
 *
 *   initialize(options)
 *   add(url, options)    Url and options passed straight to AjaxRequest.
 *                        The property options.argument becomes the argument for
 *                        the "onSuccess" notification.
 *                        EXCEPT set options.events, DO NOT USE the AjaxRequest callbacks.
 *
 *   start()
 *   busy()                           Returns true if the queue is currently running.
 *
 * Options:
 *
 *   elError    {String|HTMLElement}  Dialog displayed when a connection error occured (anything other
 *                                    than HTTP 200. Element style display will be set to 'block'.
 *                                    Must contain child element with class "uiFcAjaxError_msg" which will be
 *                                    set to the error message. Must contain a link that will enable
 *                                    use to reconnect (retry the last request, continue the queue).
 *
 *   elLoading  {String|HTMLElement}  Element displayed during ajax requests. Should contain an ajax
 *                                    loading animation icon.
 *
 *   events     {Object}              Cf. notifications.
 *
 *
 * Notifications:
 *
 *   onSuccess         Succesfull (HTTP 200 ONLY) request. callback(o, argument) where o is the
 *                     YUI Connect object (augmented by AjaxRequest with responseJSON etc),
 *                     and argument is the "argument" option supplied to add() (optional).
 *   onQueueStart
 *   onQueueEnd
 *
 */

import $$, { DomJS, domGetById, stopEvent } from "@lib/dom";
import AjaxRequest from "@old/ajaxrequest";
import EventDispatcher from "@old/eventdispatcher";

type RequestBlob = { url: string; options: Dictionary };

export default class AjaxQueue {
  blobs: RequestBlob[];

  curblob: RequestBlob | null = null;

  flow = false;

  ajaxRequest: AjaxRequest | null = null;

  ajaxIndicator: HTMLElement;

  eventDispatcher: EventDispatcher;

  $elAjaxError: DomJS<HTMLElement>;

  constructor(options: Dictionary) {
    this.blobs = [];
    this.flow = false;

    this.eventDispatcher = new EventDispatcher();
    if (options.events) {
      for (const sEvent in options.events) {
        this.eventDispatcher.connect(sEvent, options.events[sEvent]);
      }
    }

    // init error dialog
    this.$elAjaxError = $$(`#${options.elError}`);
    const $elAction = this.$elAjaxError.down("a");
    $elAction.on("click", this.reconnectEvent.bind(this));

    // init ajax loading icon
    this.ajaxIndicator = domGetById(options.elLoading);
  }

  /**
   * Add a new request to the queue.
   *
   * Parameters as for AjaxRequest constructor.
   * Cf. AjaxRequest for all options.
   *
   */
  add(url: string, options: Dictionary) {
    // set some defaults
    options.method = options.method || "post";

    options.scope = this;

    // ajax queue callbacks
    options.success = this.onAjaxSuccess;
    options.failure = this.onAjaxFailure;

    // transaction-level events
    options.customevents = {
      onStart: this.onAjaxStart,
      onComplete: this.onAjaxComplete,
    };

    this.blobs.push({
      url: url,
      options: options,
    });
  }

  start() {
    if (this.flow) {
      return false;
    }
    this.flow = true;

    this.eventDispatcher.notify("onQueueStart");

    this.next();
  }

  end() {
    this.blobs = [];
    this.flow = false;

    this.eventDispatcher.notify("onQueueEnd");
  }

  busy() {
    return this.flow === true;
  }

  next() {
    const blob = (this.curblob = this.blobs.shift() || null);

    // console.log('AjaxQueue.next(%o)', blob);

    if (blob === null) {
      this.end();
      return;
    }

    this.send(blob);
  }

  /**
   * Sends or resends (in case of reconnect) the current element in
   * the ajax queue.
   *
   * @param {Object} blob   Cf. add()
   */
  send(blob: RequestBlob) {
    // console.log('AjaxQueue.send(%o)', blob);

    this.ajaxRequest = new AjaxRequest(blob.url, blob.options);
  }

  /**
   * YUI Connect custom event.
   *
   */
  onAjaxStart(_eventType: string, _args: any[]) {
    $$(this.ajaxIndicator).css({
      position: "absolute",
      zIndex: "1000",
      display: "block",
    });
  }

  /**
   * YUI Connect custom event.
   *
   */
  onAjaxComplete(_eventType: string, _args: any[]) {
    $$(this.ajaxIndicator).css({
      display: "none",
    });
  }

  /**
   *
   * @param {Object} o   YUI Connect response object, augmented by AjaxRequest (responseJSON, ...)
   */
  onAjaxSuccess(o: Dictionary) {
    // console.log('AjaxQueue::onAjaxSuccess(%o)', o);

    this.eventDispatcher.notify("onSuccess", o, this.curblob!.options.argument);

    this.next();
  }

  /**
   *
   *
   * @param  o   YUI Connect response object, augmented by AjaxRequest (responseJSON, ...)
   */
  onAjaxFailure(o: Dictionary) {
    // console.log('AjaxQueue::onAjaxFailure(%o)', o);

    let sErrorMessage = this.ajaxRequest!.getHttpHeader(o, "RTK-Error");

    this.ajaxRequest = null;

    if (o.status === -1) {
      // show the timeout message
      this.setErrorDialog("Oops! Connection timed out.");
      return;
    }

    if (sErrorMessage) {
      //alert('Oops! The server returned an error: "'+sErr500Message+'"');

      // show neat message from custom server-side ajax exception
      this.setErrorDialog(sErrorMessage);
    } else {
      sErrorMessage = o.status + " " + o.statusText;
      // alert('Oops! The server returned a "' + sErrorMessage + '" error.');
      this.setErrorDialog(`Oops! Server ${o.status} ${o.statusText}.`);
    }
  }

  /**
   * Called when clicking link in the elAjaxError dialog.
   *
   * Retry the last message and resume the queue.
   */
  reconnectEvent(ev: Event) {
    this.setErrorDialog(false);
    this.send(this.curblob!);
    stopEvent(ev);
  }

  /**
   * Show or hide the Ajax error message.
   *
   * @param {string|boolean} sErrorMessage    String or false to hide the message.
   */
  setErrorDialog(sErrorMessage: string | false) {
    if (sErrorMessage) {
      const el = this.$elAjaxError.down(".uiFcAjaxError_msg")[0] as HTMLElement;
      el.innerHTML = sErrorMessage;
    }

    this.$elAjaxError.display(!!sErrorMessage);
  }
}
