/**
 * AjaxPanel is a wrapper for all ajax requests in the application.
 *
 * Features:
 * - centralized support for handling errors, timeouts and HTTP redirects
 * - handles communication of content between client and server for a
 *   portion of a html page (text/html), or JSON data (application/json).
 *
 * Notes:
 *
 * - Content cant be sent as a typical HTTP request or as JSON data.
 * - The response can be JSON or HTML.
 *
 * By default the panel uses HTML requests. The server receives GET/POST requests
 * and returns HTML as for standard html pages, except no <head> or <body> tags
 * should be returned.
 *
 * FORM submission:
 *
 * - By default, the first FORM found in the panel will be serialized and sent via Ajax
 *   when it is submitted (onsubmit event). To specify another FORM, use setForm() in
 *   a onSubmitForm() listener. (MUST use "initContent" for the form to be bound upon
 *   instancing AjaxPanel)
 * - If an onSubmitForm listener was registered, the listener can return false to
 *   prevent the ajax submission, and can also call get(), post() or send(). Return
 *   true to let the form submission proceed.
 * - Whenever a request uri is specified with get() and post() it will override any
 *   form that is present in the panel. So the form's action is not used, and the form
 *   contents are not serialized with the request parameters. In all other cases,
 *   the form action, method and data is used if present.
 * - By default the form is submitted with send(), which uses the method attribute of
 *   the form (get/post). To use a different method, use onSubmitForm, return false
 *   to override the default behaviour and call get() or post() without specifying
 *   the request uri.
 *
 * If not using a form, you must use the get() and post() methods, and provide a request
 * uri.
 *
 *
 * Options:
 *   form             Specifies which form to serialize when the submit event is fired.
 *                    By default the first FORM element in the panel is used (boolean true).
 *                    To use another FORM, pass a selector (string).
 *                    Use false to disable form serializing, even if one is present.
 *   events           Handlers for notifications to subscribe to (see below)
 *                    in process, otherwise a transparent layer is used (defaults FALSE).
 *   initContent      Set to true to fire onContentInit when the panel is instanced (defaults FALSE).
 *   autoFocus        Make the first INPUT .text focused during content init phase (defaults FALSE)
 *
 *
 * Notifications:
 *
 *   onResponse(tron)            Response received, BEFORE html content replace
 *                                   
 *   onFailure(error)            Response received with HTTP code NOT 2xx, AFTER the display of the error/retry message.
 *                                    
 *   onContentInit(tron)         Called after html replacement (t.getHtml()), initialize content of the panel.
 *                               Note that TRON (t) is undefined the first time, if using initContent option, since there was no response.
 *   onContentDestroy()          Called before content is replaced, cleanup widgets, events, etc.
 *   onSubmitForm(e)             A form is submitted (e is the event object). Use
 *                               e.target to identify the form element if needed.
 *                               Return false to cancel the form submission.
 *
 * Methods:
 *   getForm()                   Returns the form element that is currently observed.
 *   get(oData[, sUrl])          Do a GET request, accepts additional parameters in hash or query string format
 *   post(oData[, sUrl])         Do a POST request, accepts additional parameters in hash or query string format
 *   send(oData)                 Do either a GET or POST depending on the form's method attribute, accepts additional parameters
 *                               This method requires a form present in the panel!
 *   connect()                   Call after a failed request (get, post, send) to retry the last request
 *
 *
 * Event chain:
 *
 *   When the TRON response contains no html, the onContentDestroy() and onContentInit() cycle is not called!
 *
 *   For handling TRON responses with no html content, use onResponse().
 *
 *   0. (if initContent true) onContentInit()
 *   1. ..request..
 *   2. onResponse()
 *   3. (if html) onContentDestroy()
 *   4. (.. ....) ..content replace..
 *   5. (.. ....) onContentInit()
 *   6. Goto 1.
 *
 * TODO
 * - Add "scope" parameter for the notifications (saves writing this.xyz.bind(this) multiple times).
 *
 */

import $$, { domGetById } from "@lib/dom";
import Lang from "@lib/lang";
import AjaxIndicator from "@app/legacy/ajaxindicator";
import EventCache from "@lib/EventCache";
import EventDispatcher from "@lib/EventDispatcher";
import KoohiiLoading from "@/vue/KoohiiLoading";
import AjaxRequest from "./AjaxRequest";
import { Tron } from "@/lib/tron";

/** @typedef {import("@/lib/tron").TronInst} TronInst */

export default class AjaxPanel {
  /** @type AjaxPanelOpts */
  options = null;

  /** @type Element */
  container = null;

  /** @type EventCache */
  evtCache = null;

  /** @type EventDispatcher */
  eventDispatcher = null;

  // Custom Events instances
  events = {};

  // Form to serialize with next get() or post() call
  serializeForm = false;

  // Set true after at least one succesful html content request
  contentLoaded = false;

  /**
   *
   *
   * @param {string|HTMLElement} container     Container element where content is loaded
   * @param {AjaxPanelOpts} options                   See class doc
   */
  constructor(container, options = {}) {
    // console.log("AjaxPanel.init() options %o ", options);

    // set defaults
    this.options = {
      form: true,
      initContent: false,
      timeout: 5000,
      ...options,
    };

    // make sure to call onContentDestroy() before the first content replace, if used initContent
    this.contentLoaded = this.options.initContent === true ? true : false;

    this.container = domGetById(container);
    console.assert(this.container, "AjaxPanel::init()  container not found");

    this.evtCache = new EventCache();

    this.ajaxRequest = null;

    // register events
    this.eventDispatcher = new EventDispatcher();
    if (this.options.events) {
      var events = this.options.events,
        eventName;
      for (eventName in events) {
        this.eventDispatcher.connect(eventName, events[eventName]);
      }
    }

    this.serializeForm = this.getForm();

    if (this.options.initContent) {
      this.initContent();
    }
  }

  destroy() {
    if (this.contentLoaded) {
      this.eventDispatcher.notify("onContentDestroy");
    }

    this.evtCache.destroy();
    this.eventDispatcher.destroy();
  }

  /**
   * @param {TRON|undefined}  tron   TRON instance if loaded html, undefined if called
   *                                first time using initContent option!
   */
  initContent(tron) {
    //console.log('AjaxPanel.initContent()');

    // Attach an event to FORMs that will dispatch a "onSubmit" event.
    var elForm = (this.serializeForm = this.getForm());
    if (elForm) {
      this.evtCache.addEvent(elForm, "submit", this.submitFormEvent.bind(this));
    }

    // handle autoFocus option
    if (this.options.autoFocus) {
      this.autoFocus();
    }

    if (this.contentLoaded) {
      this.eventDispatcher.notify("onContentInit", tron);
    }
  }

  /**
   * Replace html content, if provided. Only call destroy/init methods if
   * actual content replaced.
   *
   * @param  {Object}  tron   TRON instance.
   */
  replaceContent(tron) {
    var html = tron.getHtml();

    console.log("AjaxPanel::replaceContent(html %s)", html ? "YES" : "NO");

    if (html) {
      this.evtCache.destroy();

      if (this.contentLoaded) {
        this.eventDispatcher.notify("onContentDestroy");
      }

      this.container.innerHTML = html;
      this.contentLoaded = true;

      this.initContent(tron);
    }
  }

  /**
   * Returns the form element that is currently observed.
   *
   * @return {HTMLFormElement|null}  FORM element, or null if none is observed
   */
  getForm() {
    if (this.options.form === true) {
      return $$("form", this.container)[0] || null;
    } else if (Lang.isString(this.options.form)) {
      // return the first form that matches the class name
      var form = $$(this.options.form, this.container)[0];

      console.assert(
        form,
        "AjaxPanel::getForm() form not found: `%s`",
        this.options.form
      );

      return form;
    }

    return this.options.form;
  }

  /**
   *
   * @param {SubmitEvent} evt
   */
  submitFormEvent(evt) {
    let skipSubmit = false;

    console.log("AjaxPanel.submitFormEvent() Form %o", evt.target);

    // if listener exists, and it returns false, do not auto-submit
    if (this.eventDispatcher.hasListeners("onSubmitForm")) {
      skipSubmit = false === this.eventDispatcher.notify("onSubmitForm", evt);
    }

    if (!skipSubmit) {
      this.send();
    }

    evt.preventDefault();
    evt.stopPropagation();
  }

  /**
   * Do a GET request with optional parameters to add to the request.
   *
   * @param {Object|URLSearchParams=} oData   Request parameters (optional)
   * @param {String=} sUrl    Request uri (optional), if specifed overrides the form action!
   */
  get(oData, sUrl) {
    this.prepareConnect(oData, "get", sUrl);
  }

  /**
   * Do a POST request with optional parameters to add to the request.
   *
   * @param {Object|URLSearchParams=} oData   Request parameters (optional)
   * @param {string=} sUrl    Request uri (optional), if specifed overrides the form action!
   */
  post(oData, sUrl) {
    this.prepareConnect(oData, "post", sUrl);
  }

  /**
   * Do a GET or POST request, using the active form's "method" attribute.
   *
   * @param {Object|URLSearchParams=} oData   Request parameters (optional)
   */
  send(oData) {
    var form = this.getForm();
    console.assert(form, "AjaxPanel::send()  requires valid form");
    var method = form.getAttribute("method") || "post";
    this.prepareConnect(oData, method);
  }

  /**
   *
   * @param {Object|URLSearchParams=} oData   Request parameters (optional)
   * @param {string} sMethod  Method name 'post' or 'get'
   * @param {string=} sUrl     Request uri (optional), if specifed overrides the form!
   */
  prepareConnect(oData, sMethod, sUrl) {
    var url,
      form = false,
      connectObj = {};

    // optional parameters
    if (oData) {
      connectObj.parameters = oData;
    }

    // serialize form, unless a request uri is specified
    if (!sUrl && this.serializeForm !== false) {
      form = connectObj.form = this.serializeForm;
    }

    url = sUrl || (form ? form.action : false);
    console.assert(
      url,
      "AjaxPanel::prepareConnect() No url argument and no FORM specified."
    );

    //  console.log('AjaxPanel.prepareConnect(%o, %s) FORM %o', oData, sMethod, form);
    connectObj.url = url;
    connectObj.method = sMethod;

    this.connect(connectObj);
  }

  /**
   * Establish the server connection with the current post() parameters.
   * Call with arguments to establish the connection settings.
   * Call with empty arguments to reconnect with the last settings, in case
   * the connection failed or timed out.
   *
   * Connection object:
   *   url           Url for AjaxRequest
   *   method        Method for AjaxRequest
   *   form          Form to serialize (optional)
   *   parameters    Extra GET/POST parameters
   */
  connect(oConnect) {
    if (oConnect) {
      this.connection = oConnect;
    }

    console.assert(
      this.connection,
      "AjaxPanel::connect() No connection object."
    );

    this.showLoading();

    AjaxRequest(this.connection.url, {
      method: this.connection.method,
      params: this.connection.parameters,
      form: this.connection.form,
      timeout: this.options.timeout,
    })
      .then((res) => {
        this.hideLoading();

        console.assert(!!res.status, "AjaxPanel(): not a TRON response.");
        const tron = new Tron(res.data);

        // handle TRON response
        this.eventDispatcher.notify("onResponse", tron);

        // handle HTML response (if any)
        this.replaceContent(tron);
      })
      .catch((error) => {
        this.hideLoading();

        if (error.response) {
          // The request was made and the server responded with a status code
          // that falls out of the range of 2xx
          const msg = `Oops! Error ${error.response.status}.`;
          this.showErrorMessage(msg);
        }
        
        if (error.request) {
          // The request was made but no response was received
          // `error.request` is an instance of XMLHttpRequest
          this.showErrorMessage("Oops! Timed out.");
          return;
        }

        this.eventDispatcher.notify("onFailure", error);
      });
  }

  showLoading() {
    KoohiiLoading.show({ target: this.container });
  }

  hideLoading() {
    KoohiiLoading.hide();
  }

  /**
   * Display a message in place of the ajax indicator,
   * with a "Retry" link.
   *
   * @param {Object} sMessage
   */
  showErrorMessage(sMessage) {
    this.ajaxErrorIndicator = new AjaxIndicator({
      container: this.container,
      message:
        sMessage +
        ' <a href="#" style="font-weight:bold;color:yellow;">Retry</a>',
    });
    this.ajaxErrorIndicator.show();

    var elMessage = this.ajaxErrorIndicator.getElement();
    var elRetryLink = elMessage.getElementsByTagName("a")[0];

    const retry = (_oEvent) => {
      console.log("AjaxPanel.ajaxRetryEvent()");
      this.ajaxErrorIndicator.destroy();
      this.connect();
    };

    $$(elRetryLink).on("click", retry);
  }

  /**
   * Called if the corresponding option is set, on content init phase.
   *
   * Picks the first INPUT.text element in the form, and focus() it.
   */
  autoFocus() {
    var input = $$("input.text", this.container)[0];
    if (input && typeof input.focus === "function") {
      input.focus();
    }
  }
}
