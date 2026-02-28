<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\ProductSlider;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\App\Emulation;
use Magento\Widget\Helper\Conditions;
use MageOS\Widgetkit\Block\Widgets\ProductSlider;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\State;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Review\Model\AppendSummaryDataFactory;

class Preview extends ProductSlider
{
    public function __construct(
        protected State $state,
        protected Emulation $emulation,
        protected Conditions $conditionsHelper,
        protected CollectionFactory $productCollectionFactory,
        protected LayoutFactory $layoutFactory,
        protected ReviewRenderer $reviewRenderer,
        protected AppendSummaryDataFactory $appendSummaryDataFactory,
        Context $context,
        protected ImageHelper $imageHelper,
    ) {
        parent::__construct(
            $state,
            $conditionsHelper,
            $productCollectionFactory,
            $layoutFactory,
            $reviewRenderer,
            $appendSummaryDataFactory,
            $context
        );
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
