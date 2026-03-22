/**
 *
 *   Close Button:
 *     The close button hides the dialog (not destroy()).
 *     Call show() to make the dialog visible again.
 *
 *   Draggable:
 *     The dialog is draggable by default if the header is included.
 *
 *   Options:
 *     align        to align the dialog to an anchor element
 *     dismiss      if true, clicking anywhere outside the dialog closes it
 *     close        include a close button (enables the header)
 *     mask         enable backdrop for modal dialog
 *     mobile
 *     template     must be a <template> element selector
 *     title        title for the header (enables the header)
 *     width        fixes width of the dialog, useful with async content loading
 *
 *   Classes for use on the dialog body to trigger events:
 *     JSDialogClose     click will close the dialog (calls destroy())
 *
 */
import { nextTick } from "vue";
import EventDelegator from "@/lib/EventDelegator";
import EventCache from "@/lib/EventCache";

type AlignCorner = "tl" | "tr" | "bl" | "br";

export type KoDialogAnchor = [
  anchor: HTMLElement | null,
  alignAnchor: AlignCorner,
  alignDialog: AlignCorner,
  offset?: [number, number],
];

export type KoDialogOptions = {
  align?: KoDialogAnchor;
  dismiss?: boolean;
  close?: boolean;
  mask?: boolean;
  mobile?: boolean;
  template?: string;
  title?: string;
  width?: string;
};

export default class KoDialog {
  private options: KoDialogOptions;
  private mask?: HTMLElement;
  private dialog?: HTMLElement;
  private evtCache: EventCache;
  private eventDel: EventDelegator;
  private dismissHandler: EventListener | null = null;
  private isShown: boolean = false;

  constructor(options: KoDialogOptions) {
    this.options = {
      align: undefined,
      dismiss: false,
      close: false,
      mask: false,
      mobile: false,
      template: "",
      title: "",
      ...options,
    };

    // create mask
    //  It is appended to the document BEFORE the dialog, so it shows underneath
    //  even if z-index is same as the dialog.
    if (this.options.mask) {
      this.mask = document.createElement("div");
      this.mask.className = "modal-mask";
      if (this.options.dismiss) {
        this.mask.onclick = () => this.destroy();
      }
      document.body.appendChild(this.mask);
    }

    // create dialog shell
    const dialog = document.createElement("div");
    dialog.classList.add("modal-dialog");
    this.transitionBegin(dialog);

    let header = "";
    if (this.options.title || this.options.close) {
      header = `
        <div class="modal-header" data-header>
            <span class="modal-header-title">${this.options.title}</span>
            ${
              this.options.close
                ? '<button class="modal-close-btn" data-close>&times;</button>'
                : ""
            }
        </div>
      `;
    }

    dialog.innerHTML = `
      ${header}
      <div class="modal-body"></div>
      <div class="modal-footer"></div>
    `;

    // resolve and Inject Content
    const elBody = dialog.querySelector<HTMLElement>(".modal-body")!;

    // fix width, useful for ajax loading content
    //  not for mobile (extends edge to edge)
    if (options.width && !options.mobile) {
      elBody.style.width = options.width;
    }

    if (this.options.template) {
      const template = document.querySelector<HTMLTemplateElement>(
        this.options.template
      );
      if (template) {
        const nodes = template.content.cloneNode(true);
        elBody.appendChild(nodes);
      }
    }

    // UI Listeners
    this.evtCache = new EventCache();

    if (this.options.close) {
      const elCloseBtn =
        dialog.querySelector<HTMLButtonElement>("[data-close]")!;
      this.evtCache.addEvent(elCloseBtn, "click", () => this.hide());
    }

    // setup dragging (desktop only)
    if (header && !this.options.mobile) {
      const elHeader = dialog.querySelector<HTMLElement>("[data-header]")!;
      this.setupDragging(elHeader, dialog);
    }

    this.dialog = dialog;
    document.body.appendChild(dialog);

    // handle built-in events
    this.eventDel = new EventDelegator(dialog);
    this.eventDel
      .on("click", ".JSDialogClose", () => {
        this.destroy();
        return false;
      })
      .on("click", ".JSDialogHide", () => {
        this.hide();
        return false;
      });

    // align the dialog to its anchor - ignore if mobile
    if (options.align && !options.mobile) {
      this.position(options.align);
    } else if (options.mobile) {
      // extend the dialog from edge to edge
      dialog.classList.add("is-mobile");
      dialog.style.top = "0";
      dialog.style.left = "0";
      dialog.style.right = "0";
    } else {
      dialog.style.left = "0";
      dialog.style.top = "0";
    }
  }

  setupDismiss() {
    // We use a timeout to prevent the current click event (that opened the dialog)
    // from immediately triggering the dismiss logic.
    setTimeout(() => {
      this.dismissHandler = (e: Event) => {
        if (this.dialog!.contains(e.target as HTMLElement)) {
          this.hide();
        }
      };
      document.addEventListener("click", this.dismissHandler);
    }, 0);
  }

  /**
   * Returns the body element of the dialog.
   *
   */
  getBody(): HTMLElement {
    return this.dialog!.querySelector(".modal-body")!;
  }

  getFooter(): HTMLElement {
    return this.dialog!.querySelector(".modal-footer")!;
  }

  private transitionBegin(el: HTMLElement) {
    el.classList.remove("fadein-enter-active");
    el.classList.add("fadein-enter-from");
  }

  private transitionEnd(el: HTMLElement) {
    el.classList.remove("fadein-enter-from");
    el.classList.add("fadein-enter-active");
  }

  show() {
    this.transitionBegin(this.dialog!);
    this.dialog!.style.display = "block";

    nextTick(() => {
      if (this.mask) this.mask.style.display = "block";
      this.transitionEnd(this.dialog!);
      this.isShown = true;
    });

    if (this.options.dismiss) {
      this.setupDismiss();
    }
  }

  hide() {
    this.dialog!.style.display = "none";
    if (this.mask) this.mask.style.display = "none";
    this.isShown = false;

    if (this.dismissHandler) {
      document.removeEventListener("click", this.dismissHandler);
      this.dismissHandler = null;
    }
  }

  isVisible() {
    return this.isShown;
  }

  private setupDragging(header: HTMLElement, dialog: HTMLElement) {
    let isDragging = false;
    const offset = { x: 0, y: 0 };

    const onMouseDown = (e: MouseEvent) => {
      // Don't drag if clicking the close button
      if ((e.target as HTMLElement).closest("[data-close]")) return;

      isDragging = true;

      // Get current dialog position
      const rect = dialog.getBoundingClientRect();

      // Calculate distance from mouse to top-left corner of dialog
      offset.x = e.clientX - rect.left;
      offset.y = e.clientY - rect.top;

      document.addEventListener("mousemove", onMouseMove);
      document.addEventListener("mouseup", onMouseUp);
    };

    const onMouseMove = (e: MouseEvent) => {
      if (!isDragging) return;

      // Calculate new position based on mouse and initial offset
      const left = e.clientX - offset.x;
      const top = e.clientY - offset.y;

      // Apply including current scroll
      dialog.style.left = `${left + window.scrollX}px`;
      dialog.style.top = `${top + window.scrollY}px`;
    };

    const onMouseUp = () => {
      isDragging = false;
      document.removeEventListener("mousemove", onMouseMove);
      document.removeEventListener("mouseup", onMouseUp);
    };

    this.evtCache.addEvent(header, "mousedown", onMouseDown);
  }

  private getCornerCoordinates(rect: DOMRect, cornerType: AlignCorner) {
    const map = {
      tl: { x: rect.left, y: rect.top },
      tr: { x: rect.right, y: rect.top },
      bl: { x: rect.left, y: rect.bottom },
      br: { x: rect.right, y: rect.bottom },
    };
    return map[cornerType] || map["tl"];
  }

  position(align: KoDialogAnchor) {
    const anchor = align[0];

    if (!anchor || align.length < 3) return;

    const dialog = this.dialog!;
    const dialogRect = dialog.getBoundingClientRect();
    const anchorRect = anchor.getBoundingClientRect();

    const targetPoint = this.getCornerCoordinates(anchorRect, align[1]);

    let left = targetPoint.x;
    let top = targetPoint.y;

    const dialogCorner = align[2];
    if (dialogCorner === "tr" || dialogCorner === "br")
      left -= dialogRect.width;
    if (dialogCorner === "bl" || dialogCorner === "br")
      top -= dialogRect.height;

    const offset = align[3] || [0, 0];

    dialog.style.left = `${left + window.scrollX + offset[0]}px`;
    dialog.style.top = `${top + window.scrollY + offset[1]}px`;
  }

  destroy() {
    if (this.isShown) {
      this.hide();
    }

    // clean events
    this.evtCache.destroy();
    this.eventDel.destroy();

    if (this.mask) {
      this.mask.remove();
      this.mask = undefined;
    }

    if (this.dialog) {
      this.dialog.remove();
      this.dialog = undefined;
    }
  }
}
