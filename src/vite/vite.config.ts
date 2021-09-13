import path from "path";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import strip from "@rollup/plugin-strip";
import importElementPlus from 'vite-plugin-element-plus'

// bundle all node_modules into a vendor chunk
const ROLLUP_VENDOR_CHUNK = "vendor";

// bundle all shared code with the `common` entry point
const ROLLUP_COMMON_CHUNK = "entry-common";

export default defineConfig({
  // base: "/build/dist/",

  build: {
    // output dir for production build
    outDir: path.resolve(__dirname, "../web/build/dist"),
    emptyOutDir: true,

    // emit manifest so PHP can find the hashed files
    manifest: true,

    // `false` currently doesn't output css in manifest.json
    cssCodeSplit: true,

    // custom entry points  https://rollupjs.org/guide/en/#input
    rollupOptions: {
      input: [
        "./src/entry-account.ts",
        "./src/entry-common.ts",
        "./src/entry-landing.ts",
        "./src/entry-manage.ts",
        "./src/entry-review.ts",
        "./src/entry-study.ts",
        "./src/entry-styleguide.ts",
      ],

      output: {
        /**
         * Create a "vendor" and a "common" bundles.
         *
         * This is similar to the default Vite/Rollup build, except that Rollup
         * won't create multiple chunks for shared code. All the shared functions
         * and components are grouped in a single "common" js/css files.
         *
         * This reduces the number of css/js includes generated in the php page,
         * based on Vite's manifest.json
         *
         * Based on example from https://rollupjs.org/guide/en/#outputmanualchunks
         * 
         * FIXME?  Entries/modules should have only one dot, use "foo-bar.js"
         *         not "foo.bar.js" -- or fix the name splitting code below.
         */
        manualChunks: (id, { getModuleInfo }) => {
          if (/\/node_modules\//.test(id)) {
            return ROLLUP_VENDOR_CHUNK;
          }

          const entryPoints = [];

          // We use a Set here so we handle each module at most once. This
          // prevents infinite loops in case of circular dependencies
          const idsToHandle = new Set(getModuleInfo(id).importers);

          for (const moduleId of idsToHandle) {
            const { isEntry, importers } = getModuleInfo(moduleId);
            if (isEntry) {
              entryPoints.push(moduleId);
            }

            // The Set iterator is intelligent enough to iterate over elements that
            // are added during iteration
            for (const importerId of importers) idsToHandle.add(importerId);
          }

          // For the entries (top level), we must explicitly return the entry name,
          // otherwise Rollup will create a duplicate chunk (same name, different hash)
          if (entryPoints.length === 0) {
            let entryName = `${
              id
                .split("/")
                .slice(-1)[0]
                .split(".")[0]
            }`;
            return entryName;
          }

          // If there is a unique entry, we bundle the code with that entry
          if (entryPoints.length === 1) {
            let entryName = `${
              entryPoints[0]
                .split("/")
                .slice(-1)[0]
                .split(".")[0]
            }`;
            return entryName;
          }

          // For multiple entries, we put it into a "shared" chunk
          if (entryPoints.length > 1) {
            return ROLLUP_COMMON_CHUNK;
          }
        },
      },
    },
  },

  resolve: {
    // ---------------------------------------------------------------------------
    // Set custom aliases for imports, see  https://vitejs.dev/config/#resolve-alias
    // ---------------------------------------------------------------------------
    alias: [
      { find: "@", replacement: path.resolve(__dirname, "./src") },
      {
        find: "@css",
        replacement: path.resolve(__dirname, "./src/assets/css"),
      },
      {
        find: "@img",
        replacement: path.resolve(__dirname, "./src/assets/img"),
      },
      { find: "@app", replacement: path.resolve(__dirname, "./src/app") },
      { find: "@lib", replacement: path.resolve(__dirname, "./src/lib") },
      {
        find: "@old",
        replacement: path.resolve(__dirname, "./src/app/legacy"),
      },
      // {
      //   find: "@assets",
      //   replacement: path.resolve(__dirname, "./src/assets"),
      // },
      // {
      //   find: "@styles",
      //   replacement: path.resolve(__dirname, "./src/assets/styles"),
      // },
    ],
  },

  plugins: [
    importElementPlus({}),

    // ---------------------------------------------------------------------------
    // @vitejs/plugin-vue
    // ---------------------------------------------------------------------------
    vue({
      include: [/\.vue$/, /\.md$/],
    }),

    // ---------------------------------------------------------------------------
    // @rollup/plugin-strip
    //   Removes all the console.* calls from production build
    //   https://github.com/rollup/plugins/tree/master/packages/strip
    // ---------------------------------------------------------------------------
    {
      ...strip({ include: "./src/**/*.(js|ts|vue)" }),
      apply: "build",
    },
  ],

  server: {
    // make Vite dev server reachable from outside container (same as `vite --host`)
    host: true,
  },
});
