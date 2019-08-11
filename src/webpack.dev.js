'use strict'

const isProduction = (process.env.NODE_ENV === 'production')
const webpack = require('webpack')
const merge = require('webpack-merge')
const baseConfig = require('./webpack.common.js')

module.exports = merge(baseConfig, {

  devtool: 'source-map',

  mode: 'development',

  optimization: {
    // https://webpack.js.org/plugins/split-chunks-plugin/
    splitChunks: {

      // make sure to see all expected chunks
      minSize: 1000,

      cacheGroups: {

        // Create a commons chunk, which includes all code shared between entry points.
        // commons: {
        //   name: 'commons',
        //   chunks: 'initial',
        //   minChunks: 2,
        //   enforce: true
        // },

        // Includes all code from node_modules in the whole application.
        // vendors: {
        //   test: /[\\/]node_modules[\\/]/,
        //   name: 'vendors',
        //   chunks: 'all'
        // },

        rootStyles: {
          name: 'root-bundle',
          test: (m, c, entry = 'root-bundle') =>
            m.constructor.name === 'CssModule' && recursiveIssuer(m) === entry,
          chunks: 'all',
          enforce: true,
        },
        studyStyles: {
          name: 'study-bundle',
          test: (m, c, entry = 'study-bundle') =>
            m.constructor.name === 'CssModule' && recursiveIssuer(m) === entry,
          chunks: 'all',
          enforce: true,
        },
        reviewStyles: {
          name: 'review-bundle',
          test: (m, c, entry = 'review-bundle') =>
            m.constructor.name === 'CssModule' && recursiveIssuer(m) === entry,
          chunks: 'all',
          enforce: true,
        },

      },
    },
  },

  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          'vue-style-loader',
          'css-loader'
        ]
      },

      {
        test: /\.scss$/,
        use: [
          'vue-style-loader',
          'css-loader',
          'sass-loader'
        ]
      }
    ]
  },

  plugins: [
  ],
})
