module.exports = {
  parserOptions: {
    parser: "@typescript-eslint/parser", // allows ESLint to understand TypeScript syntax
  },
  plugins: ["prettier", "vue", "@typescript-eslint"],
  extends: [
    // small set of rules which lint for well-known best-practices
    "eslint:recommended",
    // 'recommended' set of rules only from TypeScript-specific plugin (optional)
    "plugin:@typescript-eslint/recommended",
    //
    "plugin:compat/recommended",
    // parse Single File Components + Vue specific rules
    "plugin:vue/vue3-recommended",
    //
    "prettier",
  ],
  env: {
    browser: true,
    es6: true,
    node: true,
  },
  rules: {
    // we're stripping console.* calls in production build
    "no-console": "off",

    // reduce distractions when editing code
    "no-empty": "warn",
    "no-unused-vars": "off",
    "@typescript-eslint/no-empty-function": "warn",
    "@typescript-eslint/no-unused-vars": "warn",

    // this check is already covered by TS (Vetur), complains about types
    // declared in globals.d.ts
    "no-undef": "off",
    
    // we don't need to do weird things with Object.create(), kiss
    "no-prototype-builtins": "off",

    "vue/max-attributes-per-line": "off",
    "vue/no-v-html": "off",
    // "vue/require-explicit-emits": "off",
  
    // legacy js
    "no-this-alias": "off",
    //"no-var": "",
    "prefer-rest-params": "off",
    // don't care / don't like
    "prefer-const": "off",

    "@typescript-eslint/ban-ts-comment": "off",
    "@typescript-eslint/explicit-function-return-type": "off",
    "@typescript-eslint/explicit-module-boundary-types": "off",
    "@typescript-eslint/no-explicit-any": "off",
    "@typescript-eslint/no-inferrable-types": "off",
    "@typescript-eslint/no-non-null-assertion": "off",
  },
};
