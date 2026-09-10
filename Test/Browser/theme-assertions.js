(() => {
    const failures = [];
    let comparisons = 0;
    for (const mode of ['desktop', 'mobile']) {
        const reference = document.querySelector('#reference-' + mode).contentDocument;
        for (const selector of ['.text-lg', '.btn', '.grid', '.card', '.border']) {
            const expected = reference.defaultView.getComputedStyle(reference.querySelector(selector));
            const actual = getComputedStyle(document.querySelector('#preview-' + mode + ' ' + selector));
            for (const property of ['fontSize', 'fontFamily', 'lineHeight', 'color', 'backgroundColor',
                'paddingTop', 'paddingLeft', 'borderTopWidth', 'borderRadius', 'columnGap', 'gridTemplateColumns']) {
                comparisons++;
                if (actual[property] !== expected[property]) {
                    failures.push({mode, selector, property, actual: actual[property], expected: expected[property]});
                }
            }
        }
    }
    return {passed: failures.length === 0, comparisons, failures};
})()
