<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\ProductGrid;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\App\Emulation;
use Magento\Widget\Helper\Conditions;
use MageOS\Widgetkit\Block\Widgets\ProductGrid;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\State;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Review\Model\AppendSummaryDataFactory;
use MageOS\Widgetkit\Block\Widgets\ProductWidget;

class Preview extends ProductGrid
{
    /**
     * @param State $state
     * @param Emulation $emulation
     * @param Conditions $conditionsHelper
     * @param CollectionFactory $productCollectionFactory
     * @param LayoutFactory $layoutFactory
     * @param ReviewRenderer $reviewRenderer
     * @param AppendSummaryDataFactory $appendSummaryDataFactory
     * @param ProductWidget $productWidget
     * @param Context $context
     */
    public function __construct(
        protected State $state,
        protected Emulation $emulation,
        protected Conditions $conditionsHelper,
        protected CollectionFactory $productCollectionFactory,
        protected LayoutFactory $layoutFactory,
        protected ReviewRenderer $reviewRenderer,
        protected AppendSummaryDataFactory $appendSummaryDataFactory,
        protected ProductWidget $productWidget,
        Context $context,
    ) {
        parent::__construct(
            $state,
            $conditionsHelper,
            $productCollectionFactory,
            $layoutFactory,
            $reviewRenderer,
            $appendSummaryDataFactory,
            $productWidget,
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
