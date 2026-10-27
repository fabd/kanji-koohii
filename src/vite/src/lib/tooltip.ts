const timers = new WeakMap<HTMLElement, number>();

/**
 * A reusable helper for showing a short tooltip above any element.
 * The element must have `.ko-Tooltip` class.
 * The text will be set on the `data-tooltip` attribute.
 * 
 * @param el   element, must have `ko-Tooltip` class (see _tooltip.css).
 * @param text  the label to show, must be relatively short (plain text, no newlines)
 * @param ms   timeout after which the tooltip hides
 */
export function flashTooltip(el: HTMLElement, text: string, ms = 1200) {
  window.clearTimeout(timers.get(el));

  el.dataset.tooltip = text;
  el.classList.add("is-active");

  timers.set(
    el,
    window.setTimeout(() => {
      el.classList.remove("is-active");
      timers.delete(el);
    }, ms)
  );
}
