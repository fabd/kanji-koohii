module.exports = {
  extends: "stylelint-config-recommended-scss",

  rules: {
    "block-no-empty": null,
    "no-descending-specificity": null,
    "no-duplicate-selectors": null,
    "no-empty-source": null,

    "declaration-block-no-duplicate-properties": [
      true,
      // ignore fallbacks for older browsers
      { ignore: ["consecutive-duplicates"] },
    ],

    "scss/dollar-variable-pattern": "^--",
    "scss/selector-no-redundant-nesting-selector": true,
  },
};
