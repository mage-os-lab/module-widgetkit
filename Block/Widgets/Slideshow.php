<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

class Slideshow extends HyvaWidget implements BlockInterface
{
    /**
     * @param Conditions $conditionsHelper
     * @param Context $context
     * @param string $_mainTemplate
     * @param string $_navTemplate
     * @param string $_slidenavTemplate
     * @param string $_itemsTemplate
     * @param string $_itemContentTemplate
     * @param string $_itemMediaTemplate
     * @param array $data
     */
    public function __construct(
        protected Conditions $conditionsHelper,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/slideshow/templates/template.phtml',
        protected string $_navTemplate = 'MageOS_Widgetkit::widget/hyva/slideshow/templates/template-nav.phtml',
        protected string $_slidenavTemplate = 'MageOS_Widgetkit::widget/hyva/slideshow/templates/template-slidenav.phtml',
        protected string $_itemsTemplate = 'MageOS_Widgetkit::widget/hyva/slideshow/templates/template-items.phtml',
        protected string $_itemContentTemplate = 'MageOS_Widgetkit::widget/hyva/slideshow/templates/item/template-content.phtml',
        protected string $_itemMediaTemplate = 'MageOS_Widgetkit::widget/hyva/slideshow/templates/item/template-media.phtml',
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
            self::class,
            '',
            [
                '_mainTemplate' => $this->_mainTemplate,
                '_navTemplate' => $this->_navTemplate,
                '_itemsTemplate' => $this->_itemsTemplate,
                '_slidenavTemplate' => $this->_slidenavTemplate,
                '_itemMediaTemplate' => $this->_itemMediaTemplate,
                '_itemContentTemplate' => $this->_itemContentTemplate
            ]
        )->setTemplate(
            $this->_mainTemplate
        )
        ->setData(
            [
                'params' => $this->getData(),
                'items' => $this->getRepeatableField('repeatable_slideshow_items')
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
            self::class,
            '',
            [
                '_mainTemplate' => $this->_mainTemplate,
                '_navTemplate' => $this->_navTemplate,
                '_itemsTemplate' => $this->_itemsTemplate,
                '_slidenavTemplate' => $this->_slidenavTemplate,
                '_itemMediaTemplate' => $this->_itemMediaTemplate,
                '_itemContentTemplate' => $this->_itemContentTemplate
            ]
        )->setTemplate(
            $this->_navTemplate
        )
        ->setData(
            [
                'params' => $this->getData('params'),
                'items' => $this->getData('items')
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
            self::class,
            '',
            [
                '_mainTemplate' => $this->_mainTemplate,
                '_navTemplate' => $this->_navTemplate,
                '_itemsTemplate' => $this->_itemsTemplate,
                '_slidenavTemplate' => $this->_slidenavTemplate,
                '_itemMediaTemplate' => $this->_itemMediaTemplate,
                '_itemContentTemplate' => $this->_itemContentTemplate
            ]
        )->setTemplate(
            $this->_slidenavTemplate
        )
        ->setData(
            [
                'params' => $this->getData('params'),
                'items' => $this->getData('items')
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
            self::class,
            '',
            [
                '_mainTemplate' => $this->_mainTemplate,
                '_navTemplate' => $this->_navTemplate,
                '_itemsTemplate' => $this->_itemsTemplate,
                '_slidenavTemplate' => $this->_slidenavTemplate,
                '_itemMediaTemplate' => $this->_itemMediaTemplate,
                '_itemContentTemplate' => $this->_itemContentTemplate
            ]
        )->setTemplate(
            $this->_itemsTemplate
        )
        ->setData(
            [
                'params' => $this->getData('params'),
                'settings' => $itemsSettings,
                'items' => $this->getData('items')
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
            self::class,
            '',
            [
                '_mainTemplate' => $this->_mainTemplate,
                '_navTemplate' => $this->_navTemplate,
                '_itemsTemplate' => $this->_itemsTemplate,
                '_slidenavTemplate' => $this->_slidenavTemplate,
                '_itemMediaTemplate' => $this->_itemMediaTemplate,
                '_itemContentTemplate' => $this->_itemContentTemplate
            ]
        )->setTemplate(
            $this->_itemMediaTemplate
        )
            ->setData(
                [
                    'params' => $this->getData('params'),
                    'item' => $item
                ]
            )->toHtml();
    }

    /**
     * @param array $item
     * @return string
     * @throws LocalizedException
     */
    public function renderItemContent(array $item): string
    {
        return $this->getLayout()->createBlock(
            self::class,
            '',
            [
                '_mainTemplate' => $this->_mainTemplate,
                '_navTemplate' => $this->_navTemplate,
                '_itemsTemplate' => $this->_itemsTemplate,
                '_slidenavTemplate' => $this->_slidenavTemplate,
                '_itemMediaTemplate' => $this->_itemMediaTemplate,
                '_itemContentTemplate' => $this->_itemContentTemplate
            ]
        )->setTemplate(
            $this->_itemContentTemplate
        )
            ->setData(
                [
                    'params' => $this->getData('params'),
                    'item' => $item
                ]
            )->toHtml();
    }


}
