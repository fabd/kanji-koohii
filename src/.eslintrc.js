module.exports = {
  extends: [
    "eslint:recommended",
    // "plugin:prettier/recommended",
    "plugin:compat/recommended",
    "plugin:vue/recommended",
    "prettier",
    "prettier/vue",
  ],
  plugins: ["prettier", "vue"],
  env: {
    browser: true,
    es6: true,
  },
  globals: {
    // app globals
    App: "readonly", // legacy code ( lib/front/corejs/core/app.js )
    Vue: "readonly", // made available to legacy code by root bundle
    VueInstance: "readonly", // made available to legacy code by root bundle
  },
  // parser: "vue-eslint-parser",
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module",
  },
  rules: {
    // "prettier/prettier": "warn",

    // using `drop_console` for production build
    "no-console": "off",

    "no-unused-vars": "off",

    "declaration-block-no-duplicate-properties": [
      "on",
      // ignore fallbacks for older browsers
      { ignore: ["consecutive-duplicates"] },
    ],

    "vue/max-attributes-per-line": "off",
    "vue/no-v-html": "off",
  },
};
