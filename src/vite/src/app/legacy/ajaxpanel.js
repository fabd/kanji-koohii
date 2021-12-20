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
 * - During the Ajax communication, the portion of the page is covered with a
 *   layer that blocks mouse clicks. By default it is not visible (fully transparent),
 *   but can be set to shading with option 'bUseShading'.
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
 *   bUseLayer        Cover the area with a layer that blocks mouse clicks during ajax (defaults TRUE)
 *   bUseShading      If set and true, the container is darkened with a opacity layer while ajax is
 *                    in process, otherwise a transparent layer is used (defaults FALSE).
 *   initContent      Set to true to fire onContentInit when the panel is instanced (defaults FALSE).
 *   autoFocus        Make the first INPUT .text focused during content init phase (defaults FALSE)
 *
 *
 * Notifications:
 *
 *   onResponse(t)                   Ajax response received, BEFORE html content replace
 *                                    (t is TRON)
 *   onFailure(o)                    Ajax response received with HTTP code NOT 2xx, AFTER the display of the error/retry message.
 *                                    (o is YUI Connect response object WITHOUT responseJSON or responseTRON!)
 *   onContentInit(t)                Called after html replacement (t.getHtml()), initialize content of the panel.
 *                                   Note that TRON (t) is undefined the first time, if using initContent option, since
 *                                   there was no response.
 *   onContentDestroy()              Called before content is replaced, cleanup widgets, events, etc.
 *   onSubmitForm(e)                 A form is submitted (e is the event object). Use e.target
 *                                   to identify the form element if needed. Return false to cancel the default submission,
 *                                   (the listener may do a manual get/post/send with extra parameters), return a truthy value
 *                                   to proceed with the default form submission.
 *
 * Methods:
 *   getForm()                       Returns the form element that is currently observed.
 *   setForm(form)                   Set form to serialize with the next get/post/send() request (string id | HTMLElement)
 *   get(oData[, sUrl])              Do a GET request, accepts additional parameters in hash or query string format
 *   post(oData[, sUrl])             Do a POST request, accepts additional parameters in hash or query string format
 *   send(oData)                     Do either a GET or POST depending on the form's method attribute, accepts additional parameters
 *                                   This method requires a form present in the panel!
 *   connect()                       Call after a failed request (get, post, send) to retry the last request
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

import $$, { domGetById, stopEvent } from "@lib/dom";
import Lang from "@lib/lang";
import AjaxIndicator from "@old/ajaxindicator";
import AjaxRequest from "@old/ajaxrequest";
import EventCache from "@old/eventcache";
import EventDelegator from "@old/eventdelegator";
import EventDispatcher from "@old/eventdispatcher";

/** @typedef {import("@/lib/tron").TronInst} TronInst */

export default class AjaxPanel {
  /** @type AjaxPanelOpts */
  options = null;

  /** @type Element */
  container = null;

  /** @type EventCache */
  evtCache = null;

  /** @type AjaxRequest*/
  ajaxRequest = null;

  /** @type EventDispatcher */
  eventDispatcher = null;

  /** @type EventDelegator */
  eventDel = null;

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
    console.log("AjaxPanel.init() options %o ", options);

    // set defaults
    this.options = {
      ...{
        form: true,
        bUseLayer: true,
        bUseShading: false,
        initContent: false,
      },
      ...options,
    };

    // make sure to call onContentDestroy() before the first content replace, if used initContent
    this.contentLoaded = this.options.initContent === true ? true : false;

    this.container = domGetById(container);
    console.assert(this.container, "AjaxPanel::init()  container not found");

    this.evtCache = new EventCache();

    this.ajaxRequest = null;

    // event delegator to handle clicks in the panel
    this.eventDel = new EventDelegator(this.container, "click");
    this.eventDel.on("JSDialogSubmit", this.onPanelSubmit, this); // legacy support from AjaxDialog refactor

    // register events
    this.eventDispatcher = new EventDispatcher();
    if (this.options.events) {
      var events = this.options.events,
        eventName;
      for (eventName in events) {
        this.eventDispatcher.connect(eventName, events[eventName]);
      }
    }

    if (options.loadContent) {
      // load panel contents on instancing
      this.prepareConnect(null, "get", options.loadContent);
    } else {
      // assume content is already there
      this.serializeForm = this.getForm();
      if (this.options.initContent) {
        this.initContent();
      }
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

    console.log("AjaxPanel.replaceContent(html %s)", html ? "YES" : "NO");

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
   * Sets form to use with the next request (serialize data & action attribute).
   *
   * @param  {String|HTMLElement}  elForm   Form id or element
   */
  setForm(elForm) {
    elForm = domGetById(elForm);
    console.assert(
      elForm.nodeName && elForm.nodeName.toLowerCase() === "form",
      "setForm() argument 0 is not a form element"
    );

    this.serializeForm = elForm;
  }

  /**
   * Returns the form element that is currently observed.
   *
   * @return mixed  FORM element, or null if none is observed
   */
  getForm() {
    if (this.options.form === true) {
      return $$("form", this.container)[0];
    } else if (Lang.isString(this.options.form)) {
      // return the first form that matches the class name
      var form = $$(this.options.form, this.container)[0];

      console.assert(form, "AjaxPanel::getForm() form not found: `%s`", this.options.form);

      return form;
    }

    return this.options.form;
  }

  /**
   *
   * @param {Object} e   native event
   */
  submitFormEvent(evt) {
    var form,
      skipSubmit = false;

    console.log("AjaxPanel.submitFormEvent(%o) Form %o", evt, evt.target);

    // if listener exists, and it returns false, do not auto-submit
    if (this.eventDispatcher.hasListeners("onSubmitForm")) {
      skipSubmit = false === this.eventDispatcher.notify("onSubmitForm", evt);
    }

    if (!skipSubmit) {
      // set the form to serialize in the next request
      form = this.getForm();
      this.setForm(form);

      this.send();
    }

    stopEvent(evt);
  }

  /**
   * EventDelegator handler for mouse clicks on elements styled
   * liked form submit buttons.
   *
   * @param {Object} e
   * @param {Object} el
   */
  onPanelSubmit(e, el) {
    if (this.getForm()) {
      // handle onSubmitForm listener, if any is set
      this.submitFormEvent.call(this, e);
    }
    return false;
  }

  /**
   * Do a GET request with optional parameters to add to the request.
   *
   * @param {Object} oData   A hash with variables, or a query string, or undefined (optional)
   * @param {String} sUrl    Request uri (optional), if specifed overrides the form action!
   */
  get(oData, sUrl) {
    this.prepareConnect(oData, "get", sUrl);
  }

  /**
   * Do a POST request with optional parameters to add to the request.
   *
   * @param {Object} oData   A hash with variables, or a query string, or undefined (optional)
   * @param {string=} sUrl    Request uri (optional), if specifed overrides the form action!
   */
  post(oData, sUrl) {
    this.prepareConnect(oData, "post", sUrl);
  }

  /**
   * Do a GET or POST request, using the active form's "method" attribute.
   *
   * @param {Object} oData   A hash with variables, or a query string, or undefined (optional)
   */
  send(oData) {
    var form = this.getForm();
    console.assert(form, "AjaxPanel::send()  requires valid form");
    var method = form.getAttribute("method") || "post";
    this.prepareConnect(oData, method);
  }

  /**
   *
   * @param {Object} oData    A hash with post variables, or a query string, otherwise set to falsy value
   * @param {string} sMethod  Method name 'post' or 'get'
   * @param {string} sUrl     Request uri (optional), if specifed overrides the form!
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
    console.assert(url, "AjaxPanel::prepareConnect() No url argument and no FORM specified.");

    // dont send multiple requests at the same time
    if (this.ajaxRequest && this.ajaxRequest.isCallInProgress()) {
      console.warn("Previous AjaxRequest still in progress (or bug?)");
      return;
    }

    //  console.log('AjaxPanel.prepareConnect(%o, %s) FORM %o', oData, sMethod, form);

    connectObj.url = url;
    connectObj.method = sMethod;

    // start connection
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

    console.assert(this.connection, "AjaxPanel::connect() No connection object.");

    //console.log("connect ",this.options,oConnect);
    var options = {
      method: this.connection.method,
      form: this.connection.form,
      parameters: this.connection.parameters,
      nocache: true,
      timeout: this.options.timeout,

      success: this.ajaxOnSuccess,
      failure: this.ajaxOnFailure,

      customevents: {
        onStart: this.ajaxOnStart,
        onComplete: this.ajaxOnComplete,
      },
      scope: this,
    };

    this.ajaxRequest = new AjaxRequest(this.connection.url, options);
  }

  /**
   * YUI Connect custom event.
   *
   * @param {String} eventType
   * @param {Object} args
   */
  ajaxOnStart(eventType, args) {
    //console.log('AjaxPanel.ajaxOnStart(%o)', args);
  }

  /**
   * YUI Connect custom event.
   *
   * @param {String} eventType
   * @param {Object} args
   */
  ajaxOnComplete(eventType, args) {
    //console.log('AjaxPanel.ajaxOnComplete(%o)', args);
    //var response = args[0];
  }

  /**
   * Success handler.
   *
   * @param {Object} o   YUI Connect response object, augmented by AjaxRequest (responseJSON, ...)
   */
  ajaxOnSuccess(o) {
    console.log("AjaxPanel.ajaxOnSuccess(%o)", o);

    var html,
      tron = o.responseTRON;

    console.assert("AjaxPanel::ajaxOnSuccess()  Require TRON response.");

    // handle TRON response
    this.eventDispatcher.notify("onResponse", tron);

    // handle HTML response (if any)
    this.replaceContent(tron);

    // cleanup
    this.ajaxRequest = null;
  }

  /**
   * Failure handler.
   *
   * @param {Object} oAjaxResponse   YUI Connect response object WITHOUT responseJSON or responseTRON.
   */
  ajaxOnFailure(o) {
    console.log("AjaxPanel.ajaxOnFailure(%o)", o);

    // transaction aborted (timeout)
    if (o.status === -1) {
      // show the timeout message
      this.showErrorMessage("Oops! Timed out.");
      return;
    }

    var sMessage = "Oops! Error " + o.status + ' "' + o.statusText + '".';
    this.showErrorMessage(sMessage);

    this.eventDispatcher.notify("onFailure", o);

    // cleanup
    this.ajaxRequest = null;
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
      message: sMessage + ' <a href="#" style="font-weight:bold;color:yellow;">Retry</a>',
    });
    this.ajaxErrorIndicator.show();

    var elMessage = this.ajaxErrorIndicator.getElement();
    var elRetryLink = elMessage.getElementsByTagName("a")[0];

    const retry = (oEvent) => {
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
