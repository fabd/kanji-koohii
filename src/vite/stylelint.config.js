module.exports = {
  extends: [
    //  stylelint-config-recommended
    //    + stylelint-scss plugin & rules
    //    + postcss-scss custom syntqx
    // https://github.com/stylelint-scss/stylelint-config-recommended-scss
    "stylelint-config-recommended-scss",
    //
    // https://github.com/ota-meshi/stylelint-config-recommended-vue
    "stylelint-config-recommended-vue/scss",
  ],

  ignoreFiles: [
    // ignore legacy stylesheets (see README in that folder)
    "src/assets/sass/legacy/**/*.css",
    "src/assets/sass/legacy/**/*.scss",
  ],

  rules: {
    /////////////////////////////////////////////////////////////
    // stylelint
    /////////////////////////////////////////////////////////////
    "block-no-empty": null,

    // allow duplicate properties *only* on consecutive lines (ie. fallbacks for old browsers)
    "declaration-block-no-duplicate-properties": [
      true,
      {
        // ignore fallbacks for older browsers
        ignore: ["consecutive-duplicates"],
      },
    ],

    // if necessary, use `/* stylelint-disable-next-line declaration-no-important */`
    "declaration-no-important": true,

    // prevents from applying "separation of concern" to css rules
    //  (eg. organize button css by: sizes, colors, shapes, ...)
    "no-duplicate-selectors": null,
    "no-descending-specificity": null,

    // complains about `<style></style>` and things that produce no output
    "no-empty-source": null,

    /////////////////////////////////////////////////////////////
    // Control specificity
    /////////////////////////////////////////////////////////////

    // control CSS complexity
    //  - helps to read SCSS code
    //  - helps to reduce selector complexity, which is obscured by nesting (eg. `.foo ul li .baz a`)
    //  - helps to reduce specificity (coupled with SuitCSS or BEM-like convention)
    //
    "max-nesting-depth": [
      3,
      {
        // @media rules (and related mixins) that enclose a selector does not increaase
        // nesting of that selector
        ignore: ["blockless-at-rules"],
      },
    ],

    // limit the number of ID selectors in a selector to ZERO
    //  (use `/* stylelint-disable-next-line */` if necessary)
    "selector-max-id": 0,

    // limit the number of type selectors (eg. html element) to ONE
    //  - the intent is to encourage using BEM-style classes to simplify selectors
    //  - however in some situations allowing a single type selector saves
    //    from adding a lot of unnecessary classes, for example, styling content
    //    inside a card (.Card h1, .Card h2, .Card p, etc)
    //  - we want to avoid overspecifying: `.Card ul li a strong ...`
    //    which will break easily when the template is modified
    "selector-max-type": [
      1,
      {
        // allow common pattern for spacing siblings in a list like a row of buttons
        //  (ie. `.CardFooter button + button`)
        ignore: ["next-sibling"],
      },
    ],

    /////////////////////////////////////////////////////////////
    // stylelint-scss
    /////////////////////////////////////////////////////////////

    // pointless rule
    "scss/at-extend-no-missing-placeholder": null,

    // another pointless rule
    "scss/comment-no-empty": null,

    // no `margin: { left: 10px; }`
    "scss/declaration-nested-properties": "never",

    // buggy with Prettier
    "scss/operator-no-newline-after": null,

    // buggy with Prettier
    "scss/operator-no-unspaced": [true, { "severity": "warning" }],


    // sass compiler ignores `&` in `.foo & .bar`, but useful to know
    "scss/selector-no-redundant-nesting-selector": true,
  },
};
