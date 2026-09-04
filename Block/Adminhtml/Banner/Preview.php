<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Banner;

use Hyva\Theme\ViewModel\LucideIcons;
use Magento\Framework\App\Area;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\App\Emulation;
use Magento\Widget\Helper\Conditions;
use MageOS\Widgetkit\Block\Widgets\Banner;
use Magento\Framework\Exception\LocalizedException;

class Preview extends Banner
{
    /**
     * @param Emulation $emulation
     * @param Conditions $conditions
     * @param LucideIcons $lucideIcons
     * @param Context $context
     */
    public function __construct(
        protected Emulation $emulation,
        protected Conditions $conditions,
        LucideIcons $lucideIcons,
        protected Context $context
    ) {
        parent::__construct($conditions, $lucideIcons, $context);
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
