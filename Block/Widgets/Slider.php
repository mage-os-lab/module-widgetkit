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
     * @param array $data
     */
    public function __construct(
        protected Conditions $conditionsHelper,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/slider/template.phtml',
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
                'items'  => $this->getRepeatableField('repeatable_slider_items')
            ]
        )->toHtml();
    }
}
