# MageOS Widgetkit Module for Magento

Kit of CMS Widgets compatible with Hyvä frontend theme.

---

## Overview

The **Widgetkit** module defines a pre-built list of cms widgets with dedicated preview for Magento's pagebuilder.
As far as this module is full compatible with Hyvä, each preview generates Tailwind CSS style when component is loaded on pagebuilder stage, alpine JS bindings are resolved on previews too.

## 🚀 Features

1) Full support for Hyvä frontend using twind js library to compile tailwind classes at component rendering on pagebuilder stage and alpine initialization on block_cms and page_cms edit controllers.

2) Customizable slideshow widget


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
