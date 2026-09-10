# Widgetkit regression checks

Use an installed local Magento/Mage-OS project with Hyvä and the Widgetkit source under test.
Set `MAGENTO_ROOT` to that project's absolute path. Run commands below from this module's root,
using the PHP version configured for the project. No separate PHPUnit installation is required
if the project already has `vendor/bin/phpunit`.

```sh
export MAGENTO_ROOT=/absolute/path/to/magento
php "$MAGENTO_ROOT/vendor/bin/phpunit" -c phpunit.xml.dist
node Test/Browser/store-context.test.js
```

The PHP suite uses the real templates, escaping helpers and HTML Purifier. It checks encoded
XSS, unsafe attributes/SVGs, safe rich text, headings, optional images, link structure, marquee
accessibility, product eligibility/identities/row metadata, calendar overflow, theme fallback,
preview environment cleanup, and CSS preservation. It does not need a running database or
pre-generated Magento factories. Missing factories are generated in a temporary test directory
that is removed when the test process exits; the installed project's generated code is untouched.

The runtime checks require the same source installed in the project and current DI/configuration.
They read existing catalog records and render all 22 template variants. They do not create or
change CMS content, products, customers or orders; normal Magento cache/log writes still occur.
Choose existing store IDs with at least one enabled, individually visible product.

```sh
php Test/Integration/smoke.php frontend 1 /tmp/widgetkit-frontend
php Test/Integration/smoke.php adminhtml 2 /tmp/widgetkit-preview
php Test/Integration/security.php /tmp/widgetkit-security
```

The security check reproduces the PageBuilder sanitizer, AdvancedWidget serialization, widget
directive generation and frontend widget filter together. Its HTML file contains inert local
execution markers. Open it in an isolated browser: `window.__widgetkit_title`, `_content`,
`_image`, `_heading` and `_link` must remain unset. Inspect the resulting DOM for event-handler
attributes and unsafe links. This does not establish authenticated PageBuilder acceptance.

Generate browser fixtures, open the output HTML, and evaluate the matching assertion file in
the browser. Each assertion script returns a JSON object with `passed`, measurements and failures.

```sh
php Test/Browser/fixture.php /tmp/widgetkit-css.html
# Evaluate Test/Browser/preview-assertions.js in that page.

php Test/Browser/theme-fixture.php /path/to/theme/web/css/styles.css /tmp/widgetkit-theme.html
# Evaluate Test/Browser/theme-assertions.js in that page.
```

The first fixture checks CSS isolation, layers, nesting, viewport widths and font sizing. The
second compares 110 computed styles with the original compiled theme in independent desktop
and mobile iframes. Run the theme's Tailwind build as well to verify generated placement utilities.
For full acceptance, also exercise the widgets on the actual storefront and in an authenticated
PageBuilder editor. A runtime render alone does not validate the editor's drag/drop or save flow.

To check that template regressions still fail on an earlier source snapshot, set
`WIDGETKIT_TEMPLATE_ROOT` to that snapshot and run PHPUnit with `--filter RenderingTest`.
