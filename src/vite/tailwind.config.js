module.exports = {
  mode: "jit",
  purge: [
    "./src/**/*.{js,ts,vue}",
    "../apps/koohii/modules/**/templates/*.php",
    //
    //"./src/**/*.{js,jsx,ts,tsx,vue}",
  ],

  theme: {
    colors: {
      gold: {
        light: "#BAB197",
        DEFAULT: "#8A8062",
      },
    },
  },

  variants: {},
  plugins: [],
};
