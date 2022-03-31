module.exports = {
  content: [
    "./src/**/*.{js,ts,vue}",

    // Symfony's global templates (including global layout)
    "../apps/koohii/templates/*.php",

    // Symfony's per module/action templates
    "../apps/koohii/modules/**/templates/*.{md,php}",
  ],

  theme: {
    colors: {
      body: {
        // default body text (not all black)
        DEFAULT: "#42413d",
        // less contrasted body text (mainly headings atm)
        light: "#7f7d75",
      },

      danger: {
        DEFAULT: "#9f0e0b",
        dark: "#D23C3C",
        darker: "#BD2420",
      },

      shaded: {
        DEFAULT: "#e7e1d3",
      },

      success: {
        dark: "#479947",
        darker: "#2C892C",
      },

      transparent: "transparent",

      warm: {
        DEFAULT: "#87847D",
        light: "#A9A396",
      },
    },

    // redeclare to remove the baked-in line-height, and add `md` alias
    fontSize: {
      xs: "0.75rem", // 12px
      sm: "0.875rem", // 14px
      base: "1rem",
      lg: "1.125rem", // 18px

      // alias for `text-base`
      md: "1rem", // 16px

      // additional sizes in-between tailwind's
      smx: "0.9375rem", // 15px
    },

    // responsive breakpoints
    //  (MUST be kept in sync with screen() mixin in _mixins.scss)
    screens: {
      md: "768px",
      lg: "992px",
      xl: "1200px",

      // mobile/desktop switches
      mbl: { max: "991px" },
      dsk: "992px",
    },

    extend: {
      flex: {
        // can be used with flex-1 to distribute space between smaller/larger items in a row
        2: "2 2 0%",
      },

      lineHeight: {
        1: 1, // alias for `leading-none`
      },
    },
  },

  variants: {},
  plugins: [],
};
