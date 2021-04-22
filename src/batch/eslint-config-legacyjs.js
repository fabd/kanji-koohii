module.exports = {
  env: {
    browser: true,
    es6: false,
  },
  extends: "eslint:recommended",
  globals: {
    "Koohii": "readonly",
  },
  parserOptions: {
    ecmaVersion: 5,
  },
  // for legacy code "don't fix it if it's not broken".
  rules: {
    "no-extra-boolean-cast": "off",
    "no-prototype-builtins": "off",
    "no-unused-vars": "off",
    "no-useless-escape": "off",
    "no-var": "off",
    "prefer-spread": "off",
    // these shouldn't apply somehow eslint is using another config in the project
    "@typescript-eslint/camelcase": "off",
    "@typescript-eslint/no-empty-function": "off",
    "@typescript-eslint/no-this-alias": "off",
    "@typescript-eslint/no-unused-vars": "off",
  },
};
