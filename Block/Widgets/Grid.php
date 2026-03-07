<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

class Grid extends HyvaWidget implements BlockInterface
{
    public function __construct(
        protected Conditions $conditionsHelper,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/grid/templates/template.phtml',
        protected string $_itemsTemplate = 'MageOS_Widgetkit::widget/hyva/grid/templates/template-items.phtml',
        protected string $_itemMediaTemplate = 'MageOS_Widgetkit::widget/hyva/grid/templates/item/template-media.phtml',
        array $data = []
    ) {
        parent::__construct($conditionsHelper, $context, $data);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function renderMainTemplate(): string
    {
        return $this->getLayout()->createBlock(
            self::class
        )->setTemplate(
            $this->_mainTemplate
        )->setData(
            [
                'params' => $this->getData(),
                'items'  => $this->getRepeatableField('repeatable_grid_items')
            ]
        )->toHtml();
    }

    /**
     * @param array $itemsSettings
     * @return string
     * @throws LocalizedException
     */
    public function renderItems(array $itemsSettings): string
    {
        return $this->getLayout()->createBlock(
            self::class
        )->setTemplate(
            $this->_itemsTemplate
        )->setData(
            [
                'params'   => $this->getData('params'),
                'settings' => $itemsSettings,
                'items'    => $this->getData('items')
            ]
        )->toHtml();
    }

    /**
     * @param array $item
     * @return string
     * @throws LocalizedException
     */
    public function renderItemMedia(array $item): string
    {
        return $this->getLayout()->createBlock(
            self::class
        )->setTemplate(
            $this->_itemMediaTemplate
        )->setData(
            [
                'params' => $this->getData('params'),
                'item'   => $item
            ]
        )->toHtml();
    }
}
