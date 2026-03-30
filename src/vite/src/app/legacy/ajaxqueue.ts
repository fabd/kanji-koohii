/**
 * AjaxQueue handles one or more ajax requests in sequential fashion.
 *
 * - Timeout and errors will display an error dialog. Clicking reconnect will
 *   resend the last request. The success notification is only fired when the request
 *   succesfully through.
 *
 * - Server-side ajax exception `rtkAjaxException` sets message in `Rtk-Error` header.
 *   This will be shown in the connection error dialog.
 *
 * Methods:
 *
 *   initialize(options)
 *   add(url, options, argument)     Url and options passed straight to AjaxRequest.
 *                                   Optional argument is passed to the `onSuccess`
 *                                   notification.
 *
 *   start()
 *   busy()                           Returns true if the queue is currently running.
 *
 * Options:
 *
 *   elError    {String|HTMLElement}  Dialog displayed when a connection error occured.
 *                                    Should contain `<a href="#">Reconnect</a>` which
 *                                    will let user retry the request.
 *
 *   elLoading  {String|HTMLElement}  Loading indicator displayed during ajax requests.
 *
 *   onSuccess(response, argument)    Success handler for HTTP 2xx responses.
 *                                    Receives AxiosResponse.
 *
 */

import $$, { DomJS } from "@lib/dom";
import EventDispatcher from "@lib/EventDispatcher";
import AjaxRequest, { type AjaxRequestOptions } from "./AjaxRequest";
import { AxiosError, type AxiosResponse } from "axios";

type RequestBlob = {
  url: string;
  options: AjaxRequestOptions;
  argument?: string;
};

export default class AjaxQueue {
  blobs: RequestBlob[];
  curblob: RequestBlob | null = null;
  flow = false;
  ajaxIndicator: HTMLElement;
  eventDispatcher: EventDispatcher;
  $elAjaxError: DomJS<HTMLElement>;

  constructor(options: {
    elError: string;
    elLoading: string;
    onSuccess: (response: AxiosResponse, argument?: string) => void;
  }) {
    this.blobs = [];
    this.flow = false;

    this.eventDispatcher = new EventDispatcher();
    this.eventDispatcher.connect("onSuccess", options.onSuccess);

    // init error dialog
    this.$elAjaxError = $$(options.elError);
    const $elAction = this.$elAjaxError.down("a");
    $elAction.on("click", this.reconnectEvent.bind(this));

    // init ajax loading icon
    this.ajaxIndicator = $$<HTMLElement>(options.elLoading)[0]!;
  }

  add(url: string, options: AjaxRequestOptions, argument?: string) {
    console.log("AjaxQueue::add()", url, options);
    this.blobs.push({ url, options, argument });
  }

  start() {
    if (this.flow) {
      return false;
    }
    this.flow = true;

    this.next();
  }

  end() {
    this.blobs = [];
    this.flow = false;
  }

  busy() {
    return this.flow === true;
  }

  private next() {
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
  private send(blob: RequestBlob) {
    // console.log('AjaxQueue.send(%o)', blob);

    this.onAjaxStart();

    AjaxRequest(blob.url, blob.options)
      .then((response) => {
        this.eventDispatcher.notify(
          "onSuccess",
          response,
          this.curblob!.argument
        );

        this.next();
      })
      .catch((error: AxiosError) => {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        if (error.response) {
          // optional header set by rtkAjaxException
          const rtkError = error.response.headers["rtk-error"] ?? "";

          this.setErrorDialog(
            `Oops! Server error ${error.response.status} ${rtkError}.`
          );
          return;
        }

        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest
        if (error.request) {
          this.setErrorDialog("Oops! Connection timed out.");
          return;
        }

        this.setErrorDialog(`Oops! Error: ${error.message}.`);
      })
      .finally(() => {
        this.onAjaxComplete();
      });
  }

  private onAjaxStart() {
    $$(this.ajaxIndicator).css({
      position: "absolute",
      zIndex: "var(--z-toast)",
      display: "",
    });
  }

  private onAjaxComplete() {
    $$(this.ajaxIndicator).css({
      display: "none",
    });
  }

  /**
   * Called when clicking link in the Ajax error dialog.
   *
   * Retry the last message and resume the queue.
   */
  private reconnectEvent(ev: Event) {
    this.setErrorDialog(false);
    this.send(this.curblob!);
    ev.preventDefault();
  }

  /**
   * Show or hide the Ajax error message.
   *
   * @param message    Message, or false to hide the message.
   */
  private setErrorDialog(message: string | false) {
    if (message) {
      const el = this.$elAjaxError.down(".uiFcAjaxError_msg")[0]!;
      el.innerHTML = message;
    }

    this.$elAjaxError.display(!!message);
  }
}
