/**
 * MageOS Widgetkit – Tailwind module config
 *
 * Merged into the theme build by @hyva-themes/hyva-modules (v2/v3 themes).
 * For Tailwind v4 themes the equivalent configuration lives in tailwind-source.css.
 *
 * NOTE: `plugins` must be at the top level – NOT inside `purge`.
 *       `safelist` must be at `purge.safelist` – NOT at `purge.options.safelist`.
 */
module.exports = {
    purge: {
        content: [
            '../../base/templates/**/*.phtml',
        ],
        safelist: [
            /^aspect-/,
            /^duration-/,
            /^snap-/,
            /^slider-/,
            'object-cover',
            'flex-row',
            'flex-col',
            'md:flex-row',
            ...[1, 2, 3, 4, 5, 6].map(n => `[--snap-cols:${n}]`),
            ...[1, 2, 3, 4, 5, 6].map(n => `md:[--snap-cols:${n}]`),
        ],
    },
    plugins: [
        function ({ addUtilities }) {
            const utilities = {};
            for (let i = 1; i <= 6; i++) {
                utilities[`.snap-cols-${i}`] = {
                    display: 'grid',
                    gridTemplateColumns: `repeat(${i}, minmax(0, 1fr))`,
                    scrollSnapType: 'x mandatory',
                };
            }
            addUtilities(utilities, ['responsive']);
        },
    ],
}
