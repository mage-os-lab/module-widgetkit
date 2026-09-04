<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\InformationGrid;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Widget\Helper\Conditions;
use MageOS\AdvancedWidget\Block\WidgetField\Rows;

/**
 * Same repeatable-item shape as Grid\Item, with the image/image_alt/mobile_image trio
 * replaced by an icon (selected from hyva-themes/magento2-theme-module's bundled Lucide SVG
 * set - the same set MageOS_Widgetkit::product/list/item.phtml already draws on via
 * Hyva\Theme\ViewModel\LucideIcons) plus its own width/height.
 */
class Item extends Rows
{
    public function __construct(
        Conditions $conditions,
        Context $context,
        private readonly ComponentRegistrarInterface $componentRegistrar,
        array $data = [],
    ) {
        parent::__construct($conditions, $context, $data);

        $this->rows = [
            'icon' => [
                'label' => 'Icon',
                'type' => 'select',
                'description' => 'Lucide icon rendered above the title.',
                'options' => $this->getIconOptions(),
                'required' => false,
                'preview' => true,
            ],
            'icon_width' => [
                'label' => 'Icon width',
                'type' => 'text',
                'description' => 'Icon width in pixels. Leave empty to use the default (48).',
                'required' => false,
                'preview' => false,
            ],
            'icon_height' => [
                'label' => 'Icon height',
                'type' => 'text',
                'description' => 'Icon height in pixels. Leave empty to use the default (48).',
                'required' => false,
                'preview' => false,
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'required' => false,
                'preview' => true,
            ],
            'title_tag' => [
                'label' => 'Title tag',
                'type' => 'select',
                'options' => ['h3' => 'H3', 'h1' => 'H1', 'h2' => 'H2', 'h4' => 'H4', 'h5' => 'H5', 'p' => 'Paragraph', 'span' => 'Span'],
                'required' => false,
                'preview' => false,
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'textarea',
                'required' => false,
                'preview' => true,
            ],
            'button' => [
                'label' => 'Button',
                'type' => 'text',
                'description' => 'button text, no button will appear if not specified.',
                'required' => false,
                'preview' => false,
            ],
            'button_link' => [
                'label' => 'Button link',
                'type' => 'text',
                'required' => false,
                'preview' => false,
            ],
            'button_link_target' => [
                'label' => 'Button link target',
                'type' => 'select',
                'options' => ['_self' => 'Self', '_blank' => 'Blank'],
                'required' => false,
                'preview' => false,
            ],
            'use_card' => [
                'label' => 'Use card around item',
                'type' => 'select',
                'options' => [
                    'false' => 'No',
                    'true' => 'Yes',
                ],
                'required' => false,
                'preview' => false,
            ],
        ];
    }

    /**
     * Builds the icon select options by scanning hyva-themes/magento2-theme-module's own
     * bundled Lucide SVG directory directly (via ComponentRegistrar, not a hardcoded vendor
     * path) - kept in sync automatically with whatever Lucide set actually ships, instead of
     * a ~1900-line hardcoded array. Option value is the icon's kebab-case name (what
     * Hyva\Theme\ViewModel\LucideIcons::renderHtml() expects); label is a Title Case
     * approximation of the same name for readability in the admin dropdown.
     *
     * @return array<string, string>
     */
    private function getIconOptions(): array
    {
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Hyva_Theme');
        if (!$moduleDir) {
            return [];
        }

        $svgFiles = glob($moduleDir . '/view/base/web/svg/lucide/*.svg') ?: [];
        $options = [];

        foreach ($svgFiles as $svgFile) {
            $iconName = basename($svgFile, '.svg');
            $options[$iconName] = ucwords(str_replace('-', ' ', $iconName));
        }

        asort($options);

        return ['' => '-- No icon --'] + $options;
    }
}
