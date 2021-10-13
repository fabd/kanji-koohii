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
      body: {
        // default body text (not all black)
        DEFAULT: "#42413d",
        // less contrasted body text (mainly headings atm)
        light: "#7f7d85",
      },

      danger: {
        DEFAULT: "#9f0e0b",
      },

      shaded: {
        DEFAULT: "#e7e1d3",
      },
    },
  },

  variants: {},
  plugins: [],
};
