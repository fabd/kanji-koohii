import globals from "globals";
import js from "@eslint/js";
import tsEslint from "typescript-eslint";
import typescriptEslint from "@typescript-eslint/eslint-plugin";
import vuePlugin from "eslint-plugin-vue";
import tsParser from "@typescript-eslint/parser";
import vueParser from "vue-eslint-parser";

export default tsEslint.config([
  {
    // Ignore files (replaces .eslintignore)
    ignores: [
      "*.d.ts",
      "**/dist",
      "node_modules/**",
      "**/__*.js",
      "**/__*.ts",
      "**/__*.vue",
    ],
  },

  // Apply recommended JavaScript rules
  js.configs.recommended,

  //
  tsEslint.configs.recommended,

  // Apply recommended Vue 3 rules
  ...vuePlugin.configs["flat/essential"],

  // TypeScript configuration
  {
    files: ["**/*.ts", "**/*.tsx"],
    languageOptions: {
      parser: tsParser,
      parserOptions: {
        ecmaVersion: 2022,
        sourceType: "module",
        project: ["./tsconfig.json"],
        globals: {
          ...globals.browser,
        },
      },
    },
    plugins: {
      "@typescript-eslint": typescriptEslint,
    },
    rules: {
      // Disable conflicting JS rules for TS files
      "no-unused-vars": "off",
      "no-undef": "off", // TypeScript handles this
    },
  },

  // Vue TypeScript configuration
  {
    files: ["**/*.vue"],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tsParser,
        ecmaVersion: 2022,
        sourceType: "module",
      },
    },
    plugins: {
      "@typescript-eslint": typescriptEslint,
    },
    rules: {
      // Disable conflicting JS rules for TS in Vue files
      "no-unused-vars": "off",
      "no-undef": "off", // TypeScript handles this
    },
  },

  // Global configuration
  {
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: {
        // Browser globals
        window: "readonly",
        document: "readonly",
        console: "readonly",
        setTimeout: "readonly",
        localStorage: "readonly",
        confirm: "readonly",
        // Node globals for config files
        process: "readonly",
        __dirname: "readonly",
        __filename: "readonly",
        // Vite globals
        import: "readonly",
      },
    },

    rules: {
      // --------------------------------------------------------------
      // JavaScript Rules
      // --------------------------------------------------------------
      // console.* calls are stripped in production build, useful for debugging
      "no-console": "off",
      // reduce distractions when editing code
      "no-empty": "warn",

      "no-unused-vars": "off",

      "prefer-const": "warn",

      // --------------------------------------------------------------
      // Typescript rules
      // --------------------------------------------------------------
      "@typescript-eslint/no-non-null-assertion": "off",
      "@typescript-eslint/no-empty-function": "warn",
      "@typescript-eslint/no-unused-vars": [
        "warn",
        {
          "argsIgnorePattern": "^_",
          "varsIgnorePattern": "^_"
        }
      ],

      "@typescript-eslint/ban-ts-comment": "off",
      "@typescript-eslint/explicit-function-return-type": "off",
      "@typescript-eslint/explicit-module-boundary-types": "off",
      "@typescript-eslint/no-explicit-any": "off",
      "@typescript-eslint/no-inferrable-types": "off",

      // Note: you must disable the base rule as it can report incorrect errors
      "no-unused-expressions": "off",
      "@typescript-eslint/no-unused-expressions": "warn",

      // --------------------------------------------------------------
      // Vue.js Rules
      // --------------------------------------------------------------
      "vue/max-attributes-per-line": "off",
      "vue/no-v-html": "off",
    },
  },

  // File-specific overrides
  {
    files: ["**/*.vue"],
    rules: {
      indent: "off", // Let vue/html-indent handle Vue files
    },
  },
]);
