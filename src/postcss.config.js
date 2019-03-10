module.exports = {
  plugins: [
    
    // require('precss')({ /* ... */ }),

    require('cssnano')({
      preset: [ 'default', {
        
        calc: {
          // let us know if postcss can NOT reduce a calc() expression
          //  (avoid those for browser compatibility)
          warnWhenCannotResolve: true
        }

      }]
    })

  ]
};