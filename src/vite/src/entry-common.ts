/**
 * entry-common is a base bundle included in all pages of the site
 * (cf. coreWebResponse.php)
 *
 */

// common stylesheets we want across the site
import "./app/common/main.build.css";

console.log("@entry-common");

// init the site-wide navigation (desktop & mobile)
import rootBundleInit from "@/app/common/root-bundle";
rootBundleInit();

// dev only: quick color theme toggle to help preview and tweak color themes
if (import.meta.env.DEV) {
  document.addEventListener("keydown", (e) => {
    if (e.key !== "t" || e.ctrlKey || e.metaKey || e.altKey) return;

    const el = document.activeElement as HTMLElement | null;
    if (
      el &&
      (el.tagName === "INPUT" || el.tagName === "TEXTAREA" || el.isContentEditable)
    )
      return;

    const html = document.documentElement;
    html.dataset.theme = html.dataset.theme === "darkmode" ? "classic" : "darkmode";
  });
}
