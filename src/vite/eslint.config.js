// eslint.config.js

// import { defineConfig } from "eslint/config";
import eslint from "@eslint/js";
import eslintConfigPrettier from "eslint-config-prettier";
// import eslintPluginTS from "@typescript-eslint/eslint-plugin";
import eslintPluginVue from "eslint-plugin-vue";
// import globals from "globals";
import tseslint from "typescript-eslint";

// import vueTsEslintConfig from "@vue/eslint-config-typescript";

export default tseslint.config([
  eslint.configs.recommended,
  tseslint.configs.recommended,
  eslintPluginVue.configs["flat/essential"],
  eslintConfigPrettier,
  // {
  //   plugins: {
  //     eslintPluginTS,
  //     eslintPluginVue,
  //   },
  // },
  {
    name: "app/files-to-lint",
    files: ["**/*.{js,mjs,cjs,mts,ts,tsx,vue}"],
  },
  {
    name: "app/files-to-ignore",
    ignores: [
      "*.d.ts",
      "**/coverage",
      "**/dist",
      "node_modules",
      "**/__*.js",
      "**/__*.vue",
    ],
  },

  /*  {
    languageOptions: {
      ecmaVersion: "latest",
      sourceType: "module",
      globals: {
        ...globals.browser,
      },
      parser: tseslint.parser,
      parserOptions: {
        sourceType: "module",
      },
    },
  },*/

  {
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
  },
]);
