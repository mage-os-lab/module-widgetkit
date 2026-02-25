<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Slider;

use Magento\Framework\Exception\LocalizedException;

class Preview extends \MageOS\Widgetkit\Block\Widgets\Slider
{
    public function __construct(
        protected \Magento\Store\Model\App\Emulation $emulation,
        protected \Magento\Widget\Helper\Conditions $conditions,
        protected \Magento\Framework\View\Element\Template\Context $context
    ) {
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
