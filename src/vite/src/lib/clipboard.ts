/**
 * Copy text to the clipboard.
 *
 * Must be called synchronously from a user gesture (click/tap) handler, iOS
 * Safari rejects the Clipboard API outside of a user activation.
 *
 * Falls back to using execCommand("copy") for browsers that don't support the Clipboard API,
 * or when it isn't available (non-secure context, e-reader browsers).
 *
 * @param text   the text to copy
 * @param el     element whose contents are selected for the fallback
 * @returns      true if the text was copied
 */
export async function copyToClipboard(
  text: string,
  el?: HTMLElement
): Promise<boolean> {
  // first use the browser's clipboard API, which works on current desktop browsers, iOS and Android
  // (but only over HTTPS or on localhost)
  if (window.isSecureContext && navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch {
      // fall through
    }
  }

  // if clipboard API is missing or refused, or Kindle-type browsers
  return el ? execCommandCopy(el) : false;
}

function execCommandCopy(el: HTMLElement): boolean {
  const selection = window.getSelection();

  if (!selection) {
    return false;
  }

  let copied = false;
  try {
    const range = document.createRange();
    range.selectNodeContents(el);
    selection.removeAllRanges();
    selection.addRange(range);
    copied = document.execCommand("copy");
  } catch {
    copied = false;
  }

  selection.removeAllRanges();

  return copied;
}
