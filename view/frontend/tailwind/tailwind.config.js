/**
 * MageOS Widgetkit – Tailwind module config
 */
module.exports = {
  purge: {
    content: ["../../base/templates/**/*.phtml"],
    safelist: [
      /^aspect-/,
      /^duration-/,
      /^snap-/,
      /^slider-/,
      /^grid-item/,
      ...[1, 2, 3, 4, 5, 6].map((n) => `[--snap-cols:${n}]`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `md:[--snap-cols:${n}]`),
      // Grid widget column classes for all 3 breakpoints
      ...[1, 2, 3, 4, 5, 6].map((n) => `grid-cols-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `md:grid-cols-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `lg:grid-cols-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `col-start-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `md:col-start-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `lg:col-start-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `col-end-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `md:col-end-${n}`),
      ...[1, 2, 3, 4, 5, 6].map((n) => `lg:col-end-${n}`),
      // Grid gap classes
      ...[0, 1, 2, 4, 6, 8].map((n) => `gap-${n}`),
    ],
  },
  plugins: [
    function ({ addUtilities }) {
      const utilities = {};
      for (let i = 1; i <= 6; i++) {
        utilities[`.snap-cols-${i}`] = {
          display: "grid",
          gridTemplateColumns: `repeat(${i}, minmax(0, 1fr))`,
          scrollSnapType: "x mandatory",
        };
      }
      addUtilities(utilities, ["responsive"]);
    },
  ],
};
