import path from "path";
import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import strip from "@rollup/plugin-strip";
import Components from "unplugin-vue-components/vite";
import { ElementPlusResolver } from "unplugin-vue-components/resolvers";
// import { visualizer } from 'rollup-plugin-visualizer'

export default defineConfig({
  build: {
    /**
     * Note: with the default value the website doesn't display correctly
     * on my iPad Air 2 Safari. Looks like some css or js are not loaded
     * it might have to do with imports?
     *
     *   es2020   no issues displaying on iPad Air 2 Safari
     *   es2022   Vue components don't show!
     */
    target: "es2020",

    // output dir for production build
    outDir: path.resolve(__dirname, "../web/build/dist"),

    // outDir is outside root, confirm it can be emptied on build
    emptyOutDir: true,

    // generate manifest file so PHP can render links to css/js
    manifest: true,

    // `false` currently doesn't output css in manifest.json
    cssCodeSplit: true,

    // custom entry points
    rolldownOptions: {
      input: [
        "./src/entry-account.ts",
        "./src/entry-common.ts",
        "./src/entry-home.ts",
        "./src/entry-landing.ts",
        "./src/entry-manage.ts",
        "./src/entry-recognition.ts",
        "./src/entry-review.ts",
        "./src/entry-study.ts",
        "./src/entry-styleguide.ts",
      ],

      output: {
        // attempt to reduce number of chunks...
        codeSplitting: {
          minSize: 10000,
          groups: [
            {
              name: "vendor",
              test: /node_modules/,
            },
            {
              name: "vue",
              test: /src\/vue/,
            },
          ],
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
        find: "@img",
        replacement: path.resolve(__dirname, "./src/assets/img"),
      },
      { find: "@app", replacement: path.resolve(__dirname, "./src/app") },
      { find: "@lib", replacement: path.resolve(__dirname, "./src/lib") },
      {
        find: "@old",
        replacement: path.resolve(__dirname, "./src/app/legacy"),
      },
    ],
  },

  plugins: [
    // visualizer(),

    tailwindcss(),

    // ...
    Components({
      dirs: ["src/vue"],
      resolvers: [ElementPlusResolver()],
    }),

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
