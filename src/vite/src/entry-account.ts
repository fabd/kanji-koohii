import VueInstance from "@/lib/helpers/vue-instance";
import $$, { domContentLoaded } from "@/lib/dom";
import SpacedRepetitionForm from "@/app/account/SpacedRepetitionForm.vue";
import KoThemeForm from "@/app/account/KoThemeForm.vue";

domContentLoaded(() => {
  console.log("@entry-account");

  // Account > Spaced Repetition
  const elMount = $$("#JsVueMountSrsForm")[0];
  if (elMount) {
    VueInstance(SpacedRepetitionForm, elMount);
  }

  // Account > Appearance > Change theme
  const elTheme = $$("#JsVueMountThemeForm")[0];
  if (elTheme) {
    VueInstance(KoThemeForm, elTheme);
  }
});
