/**
 * Currently unused. Keeping the code for now ...
 * 
 * If using later, need to FIX the overlay div needs to be removed
 * from the document at end of hide animation otherwise it blocks
 * clicks.
 */
import $$ from "@lib/dom";

export class KoNotification {
  private element: HTMLElement;
  private static hideTimeout: NodeJS.Timeout | null = null;

  constructor() {
    // Create the div only once
    let div = $$<HTMLElement>(".ko-Notification")[0];
    if (!div) {
      div = document.createElement("div");
      div.className = "ko-Notification";
      document.body.appendChild(div);
    }

    this.element = div;
  }

  public show(message: string): void {
    // Clear any existing timeout
    if (KoNotification.hideTimeout) {
      clearTimeout(KoNotification.hideTimeout);
    }

    // Set the message and show the notification
    this.element.textContent = message;
    this.element.classList.remove("hide");

    // Trigger reflow to ensure the transition works
    void this.element.offsetWidth;

    this.element.classList.add("show");

    // Hide after 3 seconds
    KoNotification.hideTimeout = setTimeout(() => {
      this.hide();
    }, 3000);
  }

  public hide(): void {
    this.element.classList.add("hide");
  }
}
