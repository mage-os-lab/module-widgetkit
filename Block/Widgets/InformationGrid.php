<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Hyva\Theme\ViewModel\LucideIcons;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

/**
 * Same shape as Grid, but each item's image is replaced by a Lucide icon (name, width,
 * height, all admin-configurable per item - see Block/Adminhtml/InformationGrid/Item.php).
 */
class InformationGrid extends HyvaWidget implements BlockInterface
{
    private const DEFAULT_ICON_SIZE = 48;

    public function __construct(
        protected Conditions $conditionsHelper,
        protected LucideIcons $lucideIcons,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/information-grid/template.phtml',
        array $data = []
    ) {
        parent::__construct($conditionsHelper, $context, $data);
    }

    /**
     * @param string $template
     * @return void
     */
    public function setMainTemplate(string $template): void
    {
        $this->_mainTemplate = $template;
    }

    /**
     * Renders a single Lucide icon at the given size. Width/height are resolved here (not
     * left to CSS) so the icon's own intrinsic size is correct from the SVG's width/height
     * attributes, matching how MageOS_Widgetkit::product/list/item.phtml already sizes its
     * icons via the same LucideIcons view model.
     *
     * @param string|null $icon Kebab-case icon name (e.g. "shopping-cart"), empty for none
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    public function renderIcon(?string $icon, ?int $width, ?int $height): string
    {
        if (empty($icon)) {
            return '';
        }

        return $this->lucideIcons->renderHtml(
            $icon,
            '',
            $width ?: self::DEFAULT_ICON_SIZE,
            $height ?: self::DEFAULT_ICON_SIZE,
            ['aria-hidden' => 'true']
        );
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function renderMainTemplate(): string
    {
        return $this->getLayout()->createBlock(
            static::class,
            '',
            [
                '_mainTemplate' => $this->_mainTemplate,
            ]
        )->setTemplate(
            $this->_mainTemplate
        )->setData(
            [
                'params' => $this->getData(),
                'items'  => $this->getRepeatableField('repeatable_information_grid_items')
            ]
        )->toHtml();
    }
}
