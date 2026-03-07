<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Slideshow;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\App\Emulation;
use Magento\Widget\Helper\Conditions;
use MageOS\Widgetkit\Block\Widgets\Slideshow;

class Preview extends Slideshow
{
    /**
     * @param Emulation $emulation
     * @param Conditions $conditions
     * @param Context $context
     */
    public function __construct(
        protected Emulation $emulation,
        protected Conditions $conditions,
        protected Context $context
    )
    {
        return parent::__construct(
            $conditions,
            $context
        );
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function renderMainTemplate(): string
    {
        $this->emulation->startEnvironmentEmulation(
            1,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            true
        );
        $mainTemplate = parent::renderMainTemplate();
        $this->emulation->stopEnvironmentEmulation();
        return $mainTemplate;
    }
}
