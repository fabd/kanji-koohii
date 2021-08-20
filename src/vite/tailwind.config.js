module.exports = {
  mode: "jit",
  purge: [
    "./src/**/*.{js,ts,vue}",

    // Symfony's global templates (including global layout)
    "../apps/koohii/templates/*.php",

    // Symfony's per module/action templates
    "../apps/koohii/modules/**/templates/*.php",
  ],

  theme: {
    colors: {
      shaded: {
        DEFAULT: "#e7e1d3",
      },
    },
  },

  variants: {},
  plugins: [],
};
