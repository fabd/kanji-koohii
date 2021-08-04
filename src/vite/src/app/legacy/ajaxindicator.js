/**
 * Display a loading indicator in the top left corner of the container element.
 *
 * <pre>
 * Options
 *   container  {String|HTMLElement}  Parent element onto which the loading indicator is aligned.
 *                                    If not set, the indicator appears at the top right of the page.
 *   message    {String}              (Optional) Message to show in place of DEFAULT_MESSAGE, can contain html (eg. links)
 * </pre>
 *
 */

import $$, { domGetById, px } from "@lib/dom";

const DEFAULT_ZINDEX = 100;

const DEFAULT_MESSAGE = "Loading...";

class AjaxIndicator {
  constructor(options) {
    this.container =
      options && options.container
        ? domGetById(options.container)
        : document.body;
    this.message = options.message ? options.message : DEFAULT_MESSAGE;
    this.indicator = null;
  }

  destroy() {
    // remove from DOM and clear reference
    if (this.indicator && this.indicator.parentNode) {
      document.body.removeChild(this.indicator);
    }
    this.indicator = null;
  }

  show() {
    // create the element
    if (!this.indicator) {
      let { top, left } = this.container.getBoundingClientRect();

      this.indicator = document.createElement("span");
      $$(this.indicator).css({
        padding: "2px 10px",
        background: "red",
        color: "#fff",
        font: "13px/18px Arial, sans-serif",
        position: "absolute",
        left: px(left),
        top: px(top),
        "z-index": DEFAULT_ZINDEX,
        display: "block",
      });
      this.indicator.innerHTML = this.message;
      document.body.insertBefore(this.indicator, document.body.firstChild);
    }

    this.indicator.style.display = "block";
  }

  hide() {
    if (this.indicator) {
      this.indicator.style.display = "none";
    }
  }

  /**
   * Return the html element used by the ajax indicator.
   *
   * @return HTMLElement   Html element or null
   */
  getElement() {
    return this.indicator;
  }
}

export default AjaxIndicator;
