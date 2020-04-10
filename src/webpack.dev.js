"use strict";

const isProduction = process.env.NODE_ENV === "production";
const webpack = require("webpack");
const merge = require("webpack-merge");
const baseConfig = require("./webpack.common.js");

const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = merge(baseConfig, {
  devtool: "source-map",

  mode: "development",

  // module: {
  //   rules: [
  //     {
  //       test: /\.css$/,
  //       use: ["vue-style-loader", "css-loader"],
  //     },
  //     {
  //       test: /\.scss$/,
  //       use: ["vue-style-loader", "css-loader", "sass-loader"],
  //     },
  //   ],
  // },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, "css-loader"],
      },

      {
        test: /\.scss$/,
        use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
      },
    ],
  },

  optimization: {
    splitChunks: {
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/]/,
          name: "vendors-bundle",
          chunks: "all",
        },
      },
    },
  },

  // plugins: [],
  plugins: [
    new MiniCssExtractPlugin({
      filename: isProduction ? "[name].min.css" : "[name].raw.css",
      chunkFilename: isProduction ? "[name].min.css" : "[name].raw.css",
    }),
  ],
});
