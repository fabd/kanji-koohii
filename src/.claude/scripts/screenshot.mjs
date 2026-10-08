#!/usr/bin/env node
/**
 * Take a PNG screenshot of a page, or of a single element.
 *
 * Usage:
 *   node .claude/scripts/screenshot.mjs <url> [selector] <output.png> [options]
 * 
 * Login/logout in the persistent profile so Claude can take screenshots signed in:
 *   node .claude/scripts/screenshot.mjs --login http://localhost
 *
 * Options:
 *   --width=1280      viewport width
 *   --height=800      viewport height
 *   --full            full page screenshot (ignored when a selector is given)
 *   --dark            emulate prefers-color-scheme: dark
 *   --wait=0          extra delay in ms after load (eg. for transitions)
 *   --profile         use the persistent profile (keeps cookies, eg. signed in)
 *   --login           open a browser window with the persistent profile, to
 *                     sign in or out; the script exits when the window is closed
 *
 * Without --profile, each run uses a fresh, temporary profile (signed out).
 *
 * Runs on the host (not in the docker container). puppeteer-core is installed
 * in vite/node_modules. Browser defaults to Chromium; override with CHROME_PATH.
 */
import { createRequire } from "node:module";
import { existsSync, mkdirSync, mkdtempSync, rmSync } from "node:fs";
import { homedir } from "node:os";
import { join, resolve } from "node:path";

const require = createRequire(new URL("../../vite/package.json", import.meta.url));
const puppeteer = require("puppeteer-core");

const BROWSER_CANDIDATES = [
  process.env.CHROME_PATH,
  "/snap/bin/chromium",
  "/usr/bin/chromium",
  "/usr/bin/chromium-browser",
  "/usr/bin/google-chrome",
];

// snap Chromium can't read the host's /tmp, so keep profiles under $HOME
const PROFILE_ROOT = join(homedir(), "snap", "chromium", "common");
const PERSISTENT_PROFILE = join(PROFILE_ROOT, "claude-puppeteer-profile");

const DEFAULT_LOGIN_URL = "http://localhost/";

const BROWSER_ARGS = ["--no-first-run", "--no-default-browser-check"];

function usage(msg) {
  if (msg) {
    console.error(`Error: ${msg}\n`);
  }
  console.error(
    "Usage: node .claude/scripts/screenshot.mjs <url> [selector] <output.png> [--width=N] [--height=N] [--full] [--dark] [--wait=ms] [--profile]\n" +
      "       node .claude/scripts/screenshot.mjs --login [url]"
  );
  process.exit(1);
}

const flags = {};
const positional = [];
for (const arg of process.argv.slice(2)) {
  const m = arg.match(/^--([\w-]+)(?:=(.*))?$/);
  if (m) {
    flags[m[1]] = m[2] ?? true;
  } else {
    positional.push(arg);
  }
}

const executablePath = BROWSER_CANDIDATES.find((p) => p && existsSync(p));
if (!executablePath) {
  usage("no Chromium/Chrome found, set CHROME_PATH");
}

mkdirSync(PROFILE_ROOT, { recursive: true });

if (flags.login) {
  await login(positional[0] ?? DEFAULT_LOGIN_URL);
} else {
  await screenshot();
}

async function login(url) {
  mkdirSync(PERSISTENT_PROFILE, { recursive: true });
  const browser = await puppeteer.launch({
    executablePath,
    userDataDir: PERSISTENT_PROFILE,
    headless: false,
    defaultViewport: null,
    args: BROWSER_ARGS,
  });
  const [page] = await browser.pages();
  await page.goto(url);
  console.log(`Profile: ${PERSISTENT_PROFILE}`);
  console.log("Sign in or out, then close the browser window.");
  await new Promise((r) => browser.on("disconnected", r));
}

async function screenshot() {
  if (positional.length < 2 || positional.length > 3) {
    usage("expected <url> [selector] <output.png>");
  }

  const url = positional[0];
  const outPath = resolve(positional[positional.length - 1]);
  const selector = positional.length === 3 ? positional[1] : null;

  if (!outPath.endsWith(".png")) {
    usage("output path must end with .png");
  }

  const userDataDir = flags.profile
    ? PERSISTENT_PROFILE
    : mkdtempSync(join(PROFILE_ROOT, "kk-screenshot-"));

  let browser;
  try {
    browser = await puppeteer.launch({
      executablePath,
      userDataDir,
      headless: true,
      args: [...BROWSER_ARGS, "--hide-scrollbars"],
    });
  } catch (err) {
    if (flags.profile) {
      console.error("Error: could not launch browser (is the --login window still open?)");
    }
    throw err;
  }

  let exitCode = 0;
  try {
    const page = await browser.newPage();
    await page.setViewport({
      width: Number(flags.width ?? 1280),
      height: Number(flags.height ?? 800),
    });
    if (flags.dark) {
      await page.emulateMediaFeatures([{ name: "prefers-color-scheme", value: "dark" }]);
    }

    const response = await page.goto(url, { waitUntil: "networkidle0", timeout: 30000 });
    if (response && !response.ok()) {
      console.error(`Warning: HTTP ${response.status()} for ${url}`);
    }
    if (flags.wait) {
      await new Promise((r) => setTimeout(r, Number(flags.wait)));
    }

    if (selector) {
      const el = await page.waitForSelector(selector, { timeout: 5000 }).catch(() => null);
      if (!el) {
        throw new Error(`selector not found: ${selector}`);
      }
      await el.screenshot({ path: outPath });
    } else {
      await page.screenshot({ path: outPath, fullPage: Boolean(flags.full) });
    }
    console.log(outPath);
  } catch (err) {
    console.error(`Error: ${err.message}`);
    exitCode = 1;
  } finally {
    await browser.close();
    if (!flags.profile) {
      rmSync(userDataDir, { recursive: true, force: true });
    }
  }
  process.exit(exitCode);
}
