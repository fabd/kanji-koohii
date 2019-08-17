/* eslint-env node */
"use strict";

const isProduction = process.env.NODE_ENV === "production";
const webpack = require("webpack");
const merge = require("webpack-merge");
const baseConfig = require("./webpack.common.js");

module.exports = merge(baseConfig, {
  devtool: "source-map",

  mode: "development",

  module: {
    rules: [
      {
        test: /\.css$/,
        use: ["vue-style-loader", "css-loader"],
      },

      {
        test: /\.scss$/,
        use: ["vue-style-loader", "css-loader", "sass-loader"],
      },
    ],
  },

  plugins: [],
});
