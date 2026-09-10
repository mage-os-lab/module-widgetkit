(() => {
    const results = {};
    function check(name, actual, expected) {
        results[name] = {actual, expected, pass: actual === expected};
    }
    const style = (root, selector) => getComputedStyle(document.querySelector(root + ' ' + selector));
    check('outside stays unstyled', getComputedStyle(document.querySelector('#outside')).color, 'rgb(0, 0, 0)');
    check('desktop width rules apply', style('#desktop', '.responsive').display, 'grid');
    check('mobile excludes desktop rules', style('#mobile', '.responsive').display, 'block');
    check('desktop nested media applies', style('#desktop', '.nested-media').display, 'flex');
    check('mobile nested media excluded', style('#mobile', '.nested-media').display, 'block');
    for (const root of ['#desktop', '#mobile']) {
        check(root + ' layer preserved', style(root, '.layered').color, 'rgb(0, 0, 255)');
        check(root + ' nested variables preserved', style(root, '.nested strong').color, 'rgb(0, 128, 0)');
        check(root + ' storefront rem size', style(root, '.sized').fontSize, '18px');
    }
    return {passed: Object.values(results).every(result => result.pass), results};
})()
