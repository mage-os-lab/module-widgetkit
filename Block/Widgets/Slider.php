<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

class Slider extends HyvaWidget implements BlockInterface
{
    /**
     * @param Conditions $conditionsHelper
     * @param Context $context
     * @param string $_mainTemplate
     * @param string $_navTemplate
     * @param string $_slidenavTemplate
     * @param string $_itemsTemplate
     * @param string $_itemMediaTemplate
     * @param array $data
     */
    public function __construct(
        protected Conditions $conditionsHelper,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/slider/templates/template.phtml',
        protected string $_navTemplate = 'MageOS_Widgetkit::widget/hyva/slider/templates/template-nav.phtml',
        protected string $_slidenavTemplate = 'MageOS_Widgetkit::widget/hyva/slider/templates/template-slidenav.phtml',
        protected string $_itemsTemplate = 'MageOS_Widgetkit::widget/hyva/slider/templates/template-items.phtml',
        protected string $_itemMediaTemplate = 'MageOS_Widgetkit::widget/hyva/slider/templates/item/template-media.phtml',
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
                'items'  => $this->getRepeatableField('repeatable_slider_items')
            ]
        )->toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function renderNavTemplate(): string
    {
        return $this->getLayout()->createBlock(
            self::class
        )->setTemplate(
            $this->_navTemplate
        )->setData(
            [
                'params' => $this->getData('params'),
                'items'  => $this->getData('items')
            ]
        )->toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function renderSlideNavTemplate(): string
    {
        return $this->getLayout()->createBlock(
            self::class
        )->setTemplate(
            $this->_slidenavTemplate
        )->setData(
            [
                'params' => $this->getData('params'),
                'items'  => $this->getData('items')
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
