import KoDialog, { type KoDialogOptions } from "./KoDialog";
import { getApi } from "@/app/api/api";
import { type TronInst } from "@/lib/tron";
import KoohiiLoading from "@/vue/KoohiiLoading";

export class KoAjaxDialog extends KoDialog {
  private connection: {
    url: string;
    params: object | null;
    callback: (tron: TronInst<any>) => void;
  };

  constructor(
    url: string,
    params: object | null,
    options: KoDialogOptions,
    callback: (tron: TronInst<any>) => void
  ) {
    super(options);
    this.connection = { url, params, callback };
    this.connect();
  }

  connect() {
    const { url, params, callback } = this.connection;
    const target = this.getBody();

    KoohiiLoading.show({ target });

    getApi()
      .request(url, { method: "get", params })
      .then((tron) => {
        KoohiiLoading.hide();

        if (tron.isFailed()) {
          this.surfaceError(tron.getErrors());
          return;
        }

        callback(tron);
      });
  }

  surfaceError(errors: string[]) {
    console.log("KoAjaxDialog::surfaceError()");
    const message = errors.join(" ");
    const body = this.getBody();
    body.innerHTML = `
<div class="">
  <div class="bg-red-600 text-white px-2 py-1 text-md">
    ${message} <a href="#" class="text-amber-300 font-bold is-retry">Reconnect</a>
  </div>
  <div>
    
  </div>
</div>
    `;

    const elRetry = body.querySelector<HTMLElement>(".is-retry")!;
    elRetry.addEventListener("click", (_evt: Event) => {
      _evt.preventDefault();
      _evt.stopPropagation(); // fix event bubbling for dialogs with "dismiss" option
      this.getBody().innerHTML = ""; // clear bg behind KoohiiLoading spinner
      this.connect();
    });
  }
}
