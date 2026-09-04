<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Hyva\Theme\ViewModel\LucideIcons;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

/**
 * Single-banner widget (no repeatable items) with 5 layout variants, switched purely via the
 * `template` parameter - each entry template under view/base/templates/widget/hyva/banner-*.phtml
 * sets its own main template then delegates to renderMainTemplate(), same pattern GridStacked
 * uses for its v1/v2/v3 variants. Icon rendering mirrors InformationGrid's approach.
 */
class Banner extends HyvaWidget implements BlockInterface
{
    private const DEFAULT_ICON_SIZE = 48;

    public function __construct(
        protected Conditions $conditionsHelper,
        protected LucideIcons $lucideIcons,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/banner/template-full-background.phtml',
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
     * @param string|null $icon Kebab-case Lucide icon name (e.g. "shopping-cart"), empty for none
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
            ]
        )->toHtml();
    }
}
