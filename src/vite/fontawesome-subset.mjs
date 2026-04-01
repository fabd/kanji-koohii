/**
 * FontAwesome 7 Free - subsetting only required icons to reduce
 * the font file sizes. Also generates `all.min.css` with just the
 * used icons.
 *
 * Context
 *   Using fontawesome-subset since I am no longer able to download
 *   the FontAwesome 5 Pro subsetter tool (and besides, it works only
 *   on Windows and Mac).
 * 
 *   Also my FontAwesome 5 Pro backer token was disabled, so using
 *   the `fontawesome-free` package.
 *
 * Setup
 *   npm install --save-dev fontawesome-subset
 *   npm install --save-dev @fortawesome/fontawesome-free
 * 
 * Usage
 *   node fontawesome-subset.mjs
 * 
 * Outputs (to the www/ folder)
 *   ../web/fonts/fa5sub/webfonts/fa-regular-400.ttf
 *   ../web/fonts/fa5sub/webfonts/fa-regular-400.woff2
 *   ../web/fonts/fa5sub/webfonts/fa-solid-900.ttf
 *   ../web/fonts/fa5sub/webfonts/fa-solid-900.woff2
 * 
 *   ../web/fonts/fa5sub/css/all.min.css    <-- included in layout.php
 * 
 * @date   April 2026
 */
import { fontawesomeSubset } from "fontawesome-subset";
import { transform } from "lightningcss";
import fs from "fs";
import path from "path";

// the fontawesome path where we store the .woff2 / .ttf files
const OUTPUT_PATH = "../web/fonts/fa5sub";

/**
 * The FontAwesome stylesheet which has classes for all the animations,
 * transforms, and glyphs included in the font.
 */
const ALL_CSS = "./node_modules/@fortawesome/fontawesome-free/css/all.css";

const icons = {
  regular: [
    // ffs prettier
    "clock",
    "copy",
    "star",
  ],
  solid: [
    "angles-right",
    "arrow-down",
    "arrow-left",
    "arrow-right",
    "bars",
    "book-open",
    "chart-bar",
    "check",
    "chevron-down",
    "chevron-left",
    "chevron-right",
    "clock",
    "comment",
    "copy",
    "edit",
    "ellipsis-h",
    "envelope",
    "exclamation",
    "gift",
    "heart",
    "info-circle",
    "lock",
    "plus",
    "question",
    "redo",
    "search",
    "signal",
    "spinner",
    "star",
    "times",
    "user",
  ],
};

function createCssSubset(icons, inputCss, outputCss) {
  const iconSet = new Set([...icons.regular, ...icons.solid]);

  const input = fs.readFileSync(inputCss, "utf8");
  const lines = input.split("\n");

  fs.mkdirSync(path.dirname(outputCss), { recursive: true });
  const out = fs.createWriteStream(outputCss, { encoding: "utf8" });

  let count = 0;
  let buffer = [];
  let bufferedName = null;

  for (const line of lines) {
    if (bufferedName === null) {
      const m = line.match(/^\.fa-([\w-]+) \{$/);
      if (m) {
        bufferedName = m[1];
        buffer = [line];
      } else {
        out.write(line + "\n");
      }
    } else {
      buffer.push(line);

      if (line === "}") {
        // confirm it's a single --fa rule
        const isIconRule =
          buffer.length === 3 && /^\s+--fa:\s+"[^"]+";$/.test(buffer[1]);

        if (isIconRule && iconSet.has(bufferedName)) {
          console.log(`Copying CSS for icon ${bufferedName}`);
          out.write(buffer.join("\n") + "\n");
          count++;
        } else if (!isIconRule) {
          // not a simple icon rule — copy as-is
          out.write(buffer.join("\n") + "\n");
        }
        // else: icon rule not in set — discard

        buffer = [];
        bufferedName = null;
      }
    }
  }

  out.end();
  console.log(`${count} icons copied to output file`);
}

function minifyCss(inputCss) {
  const outputCss = inputCss.replace(/\.css$/, ".min.css");
  const { code } = transform({
    filename: inputCss,
    code: fs.readFileSync(inputCss),
    minify: true,
  });
  fs.writeFileSync(outputCss, code);
  const inSize = (fs.statSync(inputCss).size / 1024).toFixed(1);
  const outSize = (fs.statSync(outputCss).size / 1024).toFixed(1);
  console.log(`Minified ${path.basename(inputCss)} → ${path.basename(outputCss)} (${inSize}kb → ${outSize}kb)`);
}

fontawesomeSubset(icons, `${OUTPUT_PATH}/webfonts/`);

const OUTPUT_CSS = `${OUTPUT_PATH}/css/all.css`;

createCssSubset(icons, ALL_CSS, OUTPUT_CSS);

minifyCss(OUTPUT_CSS);
