# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 2.2.0
- Sanitize decoded rich text with HTML Purifier, escape values for their output context, and validate heading tags, custom SVGs, placement classes and slideshow duration.
- Fix "Save as Template" failing with `Attempting to parse an unsupported color function "oklch"`: downgrade oklch()/color-mix() colors in the admin widget preview stylesheet to legacy rgb()/rgba(), since PageBuilder's bundled html2canvas can't parse CSS Color 4 syntax. Only the cached admin/preview copy is touched — the storefront's own compiled theme CSS keeps its original wide-gamut colors. 
- Render previews using the CMS editor's selected store, with environment cleanup after nested renders and exceptions.
- Add product cache identities, filter disabled and individually hidden products, preserve independent repeated rows, and fix placement utilities, invalid countdown dates, empty images, nested links and marquee accessibility.
Thanks to Matt MacDougall (@mattmacrocket) for PR #14, the basis for this release's rich-text sanitization, preview-rendering, and product/countdown fixes.

## 2.1.0
### Updated
- Compatibility with sabberworm/php-css-parser ^9.0 (required by Magento 2.4.9) while keeping ^8.7 support: replace the `__toString()` casts removed in 9.0 with `render(OutputFormat)`

## 2.0.0
### Change of main logic
- Changing the core logic: switching from Twind.js to compiling CSS previews using Sabberworm in PHP. 
- Reducing the number of templates assigned to widgets: with Tailwind, it is better to avoid generating CSS classes via PHP and instead rely more heavily on PHTML markup.

## 1.4.8
### Fixed
- A11y fix for slideshow and slider widgets snap-track

## 1.4.7
### Updated
- Enhance widgets extensibility enabling every child template override through $block->setData() inside phtml 

## 1.4.6 - 2026-04-21
### Fixed
- PHP 8.4 and 8.5 compatibility: add missing parameter type in ProductWidget::loadProducts, add explicit void return type on Observer::execute, guard against null module path before substr(), add missing @throws PHPDoc on Adminhtml\Grid\Preview::renderMainTemplate

## 1.4.5
### Fixed
- Fix null dereference on product loading in ProductWidget
- Remove invalid return statement from constructors in Slider/Preview and Slideshow/Preview
- Change readonly private to protected readonly in RegisterModuleForHyvaConfig

## 1.4.4
### Updated
- Fix dotnav for slider/product-slider widgets on mobile

## 1.4.3
### Updated
- Add anchor to slider item container if button content is not set

## 1.4.2
### Fixed
- Fix slideshow min-height management

## 1.4.1
### Updated
- Add missing frontend controls on widgets parameters 

## 1.4.0
- Update composer JSON making the module installable for Magento Openso

## 1.3.2
### Updated
- Add the right file type for Tailwind 4 compatibility

## 1.3.1
### Updated
- Remove css functions from adminhtml/web/css css preview files due to incompatibility with nativa pagebuilder template sacing feature.

## 1.3.0
### Added
- Grid and Product grid widgets are available!

## 1.2.0
### Added
- product Slider widget is available!

## 1.1.3
### Fixed
- Fix dotnav navigation click issue for slider widget

## 1.1.2
### Fixed
- Fix tailwind compilation for widgetkit missing css tailwind classes

## 1.1.1
### Updated
- Slider widget minor fixes made

## 1.1.0
### Added
- Slider widget is available!

## 1.0.0
### Added
- First Commit, slideshow widget is available!
