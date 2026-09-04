<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Marquee;

use Magento\Framework\App\Area;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\App\Emulation;
use Magento\Widget\Helper\Conditions;
use MageOS\Widgetkit\Block\Widgets\Marquee;
use Magento\Framework\Exception\LocalizedException;

class Preview extends Marquee
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
    ) {
        parent::__construct($conditions, $context);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function renderMainTemplate(): string
    {
        $this->emulation->startEnvironmentEmulation(1, Area::AREA_FRONTEND, true);
        $mainTemplate = parent::renderMainTemplate();
        $this->emulation->stopEnvironmentEmulation();
        return $mainTemplate;
    }
}
