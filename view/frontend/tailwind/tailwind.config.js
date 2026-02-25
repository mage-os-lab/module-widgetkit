module.exports = {
    purge: {
        content: [
            '../../base/templates/**/*.phtml',
        ],
        options: {
            safelist: [/^aspect-/, /^duration-/, /^\[--snap-cols/, /^snap-/, /^slider-/, 'object-cover'],
        },
    }
}
