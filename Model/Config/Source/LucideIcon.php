<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model\Config\Source;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Scans hyva-themes/magento2-theme-module's own bundled Lucide SVG directory directly (via
 * ComponentRegistrar, not a hardcoded vendor path) - kept in sync automatically with whatever
 * Lucide set actually ships. Option value is the icon's kebab-case name (what
 * Hyva\Theme\ViewModel\LucideIcons::renderHtml() expects); label is a Title Case
 * approximation of the same name for readability in the admin dropdown.
 *
 * Same approach as Block/Adminhtml/InformationGrid/Item.php's own icon list, extracted here
 * as a reusable source_model so any top-level (non-repeatable) select parameter - such as
 * Banner's single icon field - can use it too.
 */
class LucideIcon implements OptionSourceInterface
{
    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar
    ) {
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Hyva_Theme');
        if (!$moduleDir) {
            return [['value' => '', 'label' => __('-- No icon --')]];
        }

        $svgFiles = glob($moduleDir . '/view/base/web/svg/lucide/*.svg') ?: [];
        $options = [];

        foreach ($svgFiles as $svgFile) {
            $iconName = basename($svgFile, '.svg');
            $options[$iconName] = ucwords(str_replace('-', ' ', $iconName));
        }

        asort($options);

        $result = [['value' => '', 'label' => __('-- No icon --')]];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }
}
