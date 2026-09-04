<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

class GridStacked extends HyvaWidget implements BlockInterface
{
    public function __construct(
        protected Conditions $conditionsHelper,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/grid-stacked/template-v1.phtml',
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
                'items'  => $this->getRepeatableField('repeatable_grid_stacked_items')
            ]
        )->toHtml();
    }
}
