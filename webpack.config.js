//

const Webpack = require("webpack");

const isProduction = (process.env.NODE_ENV === 'production');

// Extract all the processed CSS in all Vue components into a single CSS file:
//  https://vue-loader.vuejs.org/en/configurations/extract-css.html
//  https://github.com/webpack/extract-text-webpack-plugin
const ExtractTextPlugin = require("extract-text-webpack-plugin")

const Path = require('path');

module.exports = {
  
  // The base directory (absolute path!) for resolving the entry option.
  // Note: __dirname refers to the root of your project.
  //context: __dirname + "/src",

  // Here the application starts executing and webpack starts bundling
  entry: {
    'root-bundle': './lib/front/vue/vue-bundle.js',
    'study-bundle': './lib/front/vue/study-bundle.js',
    'review-bundle': './lib/front/vue/review-bundle.js'
  },

  output: {

    // Webpack bundles everything to the output.path folder...
    path: __dirname + '/web/build/pack',

    // The publicPath specifies the public URL address of the output files when referenced in a browser
    publicPath: "/build/pack/",

    // [name] : using entry name ( https://webpack.js.org/configuration/output/#output-filename )
    filename:  isProduction ? "[name].min.js" : "[name].raw.js",
  },

  module: {

    // module.rules is the same as module.loaders in 1.x
    rules: [
      {
        test:    /\.vue$/,
        use: [
          {
            loader: "vue-loader",
            options: {
              loaders: {

                css: ExtractTextPlugin.extract({
                  use: "css-loader",
                  fallback: "vue-style-loader"
                }),

                js: [
                  // testing but we won't use this
                  // {
                  //   loader: 'string-replace-loader',
                  //   options: {
                  //     multiple: [
                  //       { search: '\\sCore\\.log\\(', replace: '//Core.log(', flags: 'g' },
                  //       { search: '\\sCore\\.warn\\(', replace: '//Core.warn(', flags: 'g' }
                  //     ]
                  //   }
                  // },
                  {
                    loader: 'babel-loader'
                  }
                  
                ]
              }
            }
          }
        ]
      },

      {
        // Ask webpack to check: If this file ends with .js, then apply some transforms
        test:    /\.js$/,
        exclude: /node_modules/,
        use: [
          // testing but we won't use this
          // {
          //   loader: 'string-replace-loader',
          //   options: {
          //     multiple: [
          //       { search: '\\sCore\\.log\\(', replace: '//Core.log(', flags: 'g' },
          //       { search: '\\sCore\\.warn\\(', replace: '//Core.warn(', flags: 'g' }
          //     ]
          //   }
          // },
          {
            loader: 'babel-loader'
          }
        ]
      },

      {
        test:    /\.(png|jpg|gif|svg)$/,
        use: [ {
          loader:  'file-loader',
          options: {
            name: '[name].[ext]?[hash]'
          }
        } ]
      }
    ]
  },

  plugins: [
    // enable Scope Hoisting (very small gains)
    new Webpack.optimize.ModuleConcatenationPlugin(),

    // https://webpack.js.org/plugins/commons-chunk-plugin/
    new Webpack.optimize.CommonsChunkPlugin({
      name: 'root-bundle',
      minChunks: Infinity
    }),

    // https://webpack.js.org/plugins/extract-text-webpack-plugin/
    new ExtractTextPlugin({
      filename: '[name].css'
    }),

    // remove all debug code (including Vue.js warnings) in production
    new Webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: isProduction ? '"production"' : '"development"'
      }
    })
  ],

  resolve: {
    // aliases used in lib/front/vue/vue-bundle.js
    alias: {
      // Soon we may use the "runtime" (needs to fix Leitner chart component)
      //  'runtime' = 88.1 kb minified, 'common' = 99.9 kb minified
      //vue:     'vue/dist/vue.runtime.js'
      'vue':     'vue/dist/vue.common.js',

      'components': Path.resolve(__dirname, 'lib/front/vue/components'),
      'legacy':     Path.resolve(__dirname, 'lib/front/vue/legacy'),
      'lib':        Path.resolve(__dirname, 'lib/front/vue/lib')
    }
  },

  performance: {
    // turn off asset size warnings in development build
    // webpack 2.3.2 "npm run build" chokes on this...
    //hints: isProduction
  }
}

if (isProduction)
{
  const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

  module.exports.plugins = (module.exports.plugins || []).concat([

    // https://github.com/webpack-contrib/uglifyjs-webpack-plugin#uglifyoptions
    // https://github.com/mishoo/UglifyJS2/tree/harmony#compress-options
    new UglifyJsPlugin({
      uglifyOptions: {
        warnings: false,
        compress: {
          drop_console: true,
          // pure_funcs: [ 'Core.log' ],
          warnings: false
        }
      }
    }),
    new Webpack.LoaderOptionsPlugin({
      minimize: true
    })
  ])
}
