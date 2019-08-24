"use strict";

const isProduction = process.env.NODE_ENV === "production";

const path = require("path");

const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const StylelintBarePlugin = require("stylelint-bare-webpack-plugin");
const VueLoaderPlugin = require("vue-loader/lib/plugin");

module.exports = {
  // turn off noisy MiniCssExtractPlugin
  stats: { children: false },

  // note: live reload doesn't work, use --watch
  devServer: {
    contentBase: "web",
  },

  entry: {
    "landing-bundle": "./lib/front/vue/landing-bundle.js",
    "root-bundle": "./lib/front/vue/root-bundle.js",
    "study-bundle": "./lib/front/vue/study-bundle.js",
    "review-bundle": "./lib/front/vue/review-bundle.js",
  },

  output: {
    path: path.resolve(__dirname, "web/build/pack/"),

    // [name] : using entry name ( https://webpack.js.org/configuration/output/#output-filename )
    filename: isProduction ? "[name].min.js" : "[name].raw.js",

    // prefixed to every URL created by the runtime or loaders (ends with / in most cases)
    publicPath: "/build/pack/",

    // determine the name of non-entry chunk files
    //  https://webpack.js.org/configuration/output/#outputchunkfilename
    chunkFilename: isProduction ? "[name].min.js" : "[name].raw.js",
  },

  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: "vue-loader",
      },

      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        use: {
          // https://webpack.js.org/loaders/babel-loader/#usage
          loader: "babel-loader",
          options: {
            cacheDirectory: true, // node_modules/.cache/babel-loader/
            presets: [
              [
                "@babel/preset-env",
                {
                  modules: false,
                  corejs: {
                    version: 3,
                    proposals: true,
                  },
                  useBuiltIns: "usage",
                },
              ],
            ],
            // plugins: [
            //   ['component',
            //     {
            //       libraryName: 'element-ui',
            //       styleLibraryName: 'theme-chalk'
            //     }
            //   ]
            // ]
          },
        },
      },

      {
        test: /\.(png|jpg|gif|svg)$/,
        use: [
          {
            loader: "file-loader",
            options: {
              name: "[name].[ext]",
            },
          },
        ],
      },

      {
        test: /\.(ttf|eot|woff2?)$/i,
        use: [
          {
            loader: "file-loader",
            options: {
              name: "fonts/[name].[ext]",
            },
          },
        ],
      },
    ],
  },

  plugins: [
    new CleanWebpackPlugin(),
    // new DashboardPlugin(),
    new StylelintBarePlugin({
      // cf. stylelint-webpack-plugin options
      files: ["lib/front/vue/**/*.vue", "web/koohii/**/*.{css,scss}"],
      lintDirtyModulesOnly: true,
    }),
    new VueLoaderPlugin(),
  ],

  resolve: {
    extensions: [".js", ".vue", ".json"],

    alias: {
      // Soon we may use the "runtime" (needs to fix Leitner chart component)
      //  'runtime' = 88.1 kb minified, 'common' = 99.9 kb minified
      // vue:     'vue/dist/vue.runtime.esm.js',
      vue: "vue/dist/vue.common.js",

      "@components": path.resolve(__dirname, "lib/front/vue/components"),
      "@legacy": path.resolve(__dirname, "lib/front/vue/legacy"),
      "@lib": path.resolve(__dirname, "lib/front/vue/lib"),
      "@web": path.resolve(__dirname, "web"),
    },
  },
};
