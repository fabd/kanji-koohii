module.exports = {
  parserOptions: {
    parser: "@typescript-eslint/parser", // allows ESLint to understand TypeScript syntax
  },
  plugins: ["prettier", "vue", "@typescript-eslint"],
  extends: [
    // small set of rules which lint for well-known best-practices
    "eslint:recommended",
    // disables a few of the 'recommended' rules already covered by TypeScript's typechecker
    "plugin:@typescript-eslint/eslint-recommended",
    // 'recommended' set of rules only from TypeScript-specific plugin (optional)
    "plugin:@typescript-eslint/recommended",
    //
    "plugin:compat/recommended",
    // parse Single File Components + Vue specific rules
    "plugin:vue/recommended",
    //
    "prettier",
    // adds Vue specific formatting rules to prettier
    "prettier/vue",
  ],
  env: {
    browser: true,
    es6: true,
    node: true,
  },
  globals: {
    // App: "readonly", // legacy code ( lib/front/corejs/core/app.js )
    // Vue: "readonly", // made available to legacy code by root bundle
    // VueInstance: "readonly", // made available to legacy code by root bundle
  },
  rules: {
    "no-console": "off", // using `drop_console` for production build
    "no-unused-vars": "off",
    "vue/max-attributes-per-line": "off",
    "vue/no-v-html": "off",
    // legacy js
    "no-this-alias": "off",
    //"no-var": "",
    "prefer-rest-params": "off",
    // don't care / don't like
    "prefer-const": "off",
    // less verbose TS checks
    "@typescript-eslint/camelcase": "warn",
    "@typescript-eslint/explicit-function-return-type": "off",
    "@typescript-eslint/no-explicit-any": "off",
    "@typescript-eslint/no-non-null-assertion": "off",
  },
};
