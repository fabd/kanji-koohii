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
        DEFAULT: "#42413d", // FIGMA "Text/Body" (default body text, not 100% black)
        light: "#7f7d75", // FIGMA "Text/Headings" (less contarsted body text, headings)
      },

      danger: {
        DEFAULT: "#9f0e0b",
        dark: "#D23C3C",
        darker: "#BD2420",
      },

      link: "#005cb1", // FIGMA "Text/Link"

      shaded: {
        DEFAULT: "#e7e1d3", // FIGMA "Bg/Light"
        dark: "#d4cdba", // FIGMA *TODO*
      },

      success: {
        dark: "#3a7c3a",
        darker: "#2C892C",
      },

      transparent: "transparent",

      warm: {
        DEFAULT: "#87847D",
        light: "#A9A396",
      },

      // dashboard & other stat boxes
      dash: {
        // separator line in dashboard panes (#A9A396 / 59% opacity)
        line: "#c2bdaf",
      },
    },

    // redeclare to remove the baked-in line-height, and add `md` alias
    fontSize: {
      xs: "0.75rem", // 12px
      sm: "0.875rem", // 14px
      base: "1rem",
      lg: "1.125rem", // 18px
      xl: "1.25rem", // 20px
      "2xl": "1.5rem", // 24px

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
