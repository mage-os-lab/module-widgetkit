# MageOS Widgetkit Module for Magento

Kit of CMS Widgets compatible with Hyvä frontend theme.

---

## Overview

The **Widgetkit** module defines a pre-built list of cms widgets with dedicated preview for Magento's pagebuilder.
As far as this module is full compatible with Hyvä, each preview generates Tailwind CSS style when component is loaded on pagebuilder stage, alpine JS bindings are resolved on previews too.

## 🚀 Features

1) Full support for Hyvä frontend: PageBuilder previews render each widget's real, already-compiled theme Tailwind CSS (no runtime CSS compilation) and initialize Alpine.js bindings on the `cms_block`/`cms_page` edit controllers. The preview theme is picked automatically per CMS page/block: it uses the Hyvä theme assigned to the entity's own store view when there is one, otherwise falls back to an admin-configurable default (Stores → Configuration → MageOS Widgetkit → PageBuilder Preview), whose theme list only ever offers Hyvä child themes.

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

## 🤝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
