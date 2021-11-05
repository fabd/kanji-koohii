/**
 *
 *   onActionCallback(actionId, ev)
 *      Listener for clicks on elements with "uiFcAction" class, the action is specified
 *      in `data-action`.
 *
 *      Also listen to actions linked to keyboard shortcuts, set with addShortcutKey().
 *
 *      This listener must explicitly return false to stop event from propagating.
 *
 *
 * Example
 *
 *    <button class="uiFcAction" data-action="flip">Flip card</button>
 *
 */
// @ts-check

import { getBodyED } from "@app/root-bundle";
import Keyboard from "@old/keyboard";

export default class ReviewPage {
  /** @type {Function} */
  onActionCallback;

  /**
   *
   * @param {Function} onActionCallback
   */
  constructor(onActionCallback) {
    this.onActionCallback = onActionCallback;

    this.oKeyboard = new Keyboard();

    // handler for custom actions on the page with `.uiFcAction` elements
    var ed = getBodyED();
    ed.on("uiFcAction", this.onActionEvent, this);
  }

  /**
   *
   * @param {string} action ... action id (from `data-action` on the .uiFcAction element)
   * @param {Event} evt ... click or keyboard event triggering the action
   * @returns {boolean} ... related to the EventDelegator (not really needed)
   */
  notifyAction(action, evt) {
    return false !== this.onActionCallback(action, evt);
  }

  /**
   * Register a shortcut key for an action id. Pressing the given key
   * will notify 'onAction' with the given action id. Lowercase letters will match
   * the uppercase letter.
   *
   * @param {string | number} sKey  Shortcut key, should be lowercase, or ' ' for spacebar
   * @param {string} sActionId  Id passed to the 'onAction' event when key is pressed
   */
  addShortcutKey(sKey, sActionId) {
    this.oKeyboard.addListener(sKey, (event) => {
      this.notifyAction(sActionId, event);
    });
  }

  /**
   * The event listener bound to html elements that use "uiFcAction-XXX" class names.
   *
   * Makes sure to stop the mouse click event, to prevent page from jumping.
   *
   * @param  {Event}      ev   Event object
   * @param  {HTMLElement} el   Matched element
   * @return {boolean}
   */
  onActionEvent(ev, el) {
    const actionId = el.dataset.action;
    console.assert(!!actionId, 'onActionEvent() bad "action" attribute, element %o', el);
    return this.notifyAction(/** @type {string}*/ (actionId), ev);
  }
}
