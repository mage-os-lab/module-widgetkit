# MageOS Widgetkit Module for Magento

Kit of CMS Widgets compatible with Hyvä frontend theme.

---

## Overview

Widgetkit provides CMS widgets for Hyvä storefronts and previews for Magento PageBuilder.

## 🚀 Features

1) Hyvä frontend widgets: PageBuilder previews render each widget's real, already-compiled theme Tailwind CSS (no runtime CSS compilation) and initialize Alpine.js bindings on the `cms_block`/`cms_page` edit controllers. The preview theme is picked automatically per CMS page/block: it uses the Hyvä theme assigned to the entity's own store view when there is one, otherwise falls back to an admin-configurable default (Stores → Configuration → MageOS Widgetkit → PageBuilder Preview), whose theme list only ever offers Hyvä child themes.

2) Customizable slideshow widget

3) Customizable slider widget

4) Customizable product slider widget

5) Customizable grid widget

6) Customizable product grid widget

7) Customizable information grid widget – icon, title, description and a CTA button per item

8) Customizable accordion widget – collapsible content sections

9) Customizable marquee widget – infinitely scrolling strip of logos/content

10) Customizable tab widget – switchable content panels

11) Customizable stacked grid widget – full-width stacked rows, in 3 layout variants

12) Customizable countdown widget – counts down to a target date

13) Customizable banner widget – image + title/text/CTA, in 5 layout variants

## 🔧 Installation

1. Install it into your Mage-OS/Magento 2 project with composer:
    ```
    composer require mage-os/module-widgetkit
    ```

2. Enable module
    ```
    bin/magento module:enable MageOS_Widgetkit
    bin/magento setup:upgrade
    ```

3. Regenerate the Hyvä source configuration and build your project's child theme:
    ```
    bin/magento hyva:config:generate
    ```
    Run the theme's normal Tailwind build after installing or updating Widgetkit. The module
    supplies Tailwind v3 safelists and v4 sources, including responsive column placement.
    Widgetkit does not compile storefront CSS during a request.

## Preview rendering and content safety

Previews use the store selected in the CMS editor, or the entity's first assigned store, with
an active default storefront as fallback. Store, theme and locale emulation covers the entire
preview and is restored even when rendering fails. Composer themes and inherited stylesheets
are resolved through Magento's asset fallback system.

Preview CSS retains its layers and nested rules. Browser [`@scope`](https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/At-rules/%40scope)
keeps theme selectors within each preview, and [container queries](https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Containment/Container_queries)
evaluate width breakpoints against the PageBuilder stage. Use a browser supporting both features.
The default storefront root font size is 16px; themes with a different baseline can configure
`MageOS\Widgetkit\Model\PreviewCss`'s `rootFontSize` DI argument. CSS imports must be compiled.
Unsupported mixed width/device boolean conditions fail with a logged warning rather than
silently applying desktop styles to mobile previews. Preview CSS does not depend on Sabberworm,
so it can coexist with Mage-OS 3.5's CSS parser dependency.

The preview theme selector continues to list Hyvä **child themes**. An installation that uses
`Hyva/default` directly needs a configured child theme for styled PageBuilder previews.

Plain text is escaped after widget serialization is decoded. WYSIWYG content is sanitized by
HTML Purifier after decoding, preserving supported formatting and safe links/images. Scripts,
event handlers, embedded active content and unsafe URL protocols are removed. Custom accordion
SVG icons accept only self-contained shapes; invalid icons use the default chevron. Custom CSS
classes still need to be included in the theme build.

## Verification

See [Test/README.md](Test/README.md) for unit, runtime and browser regression checks.

## 🤝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
