# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

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
