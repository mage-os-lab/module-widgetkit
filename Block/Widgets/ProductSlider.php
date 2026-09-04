<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Review\Model\AppendSummaryDataFactory;
use MageOS\Widgetkit\Block\Widgets\ProductWidget;

class ProductSlider extends HyvaWidget implements BlockInterface
{

    /**
     * @param State $state
     * @param Conditions $conditionsHelper
     * @param CollectionFactory $productCollectionFactory
     * @param LayoutFactory $layoutFactory
     * @param ReviewRenderer $reviewRenderer
     * @param AppendSummaryDataFactory $appendSummaryDataFactory
     * @param ProductWidget $productWidget
     * @param Context $context
     * @param string $_mainTemplate
     * @param array $data
     */
    public function __construct(
        protected State $state,
        protected Conditions $conditionsHelper,
        protected CollectionFactory $productCollectionFactory,
        protected LayoutFactory $layoutFactory,
        protected ReviewRenderer $reviewRenderer,
        protected AppendSummaryDataFactory $appendSummaryDataFactory,
        protected ProductWidget $productWidget,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/product-slider/template.phtml',
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
     * @return LayoutInterface
     * @throws LocalizedException
     */
    public function getProductItemLayout(): LayoutInterface
    {
        return $this->productWidget->getProductItemLayout();
    }

    /**
     * @param Product $product
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function renderProductItem(Product $product): string
    {
        return $this->productWidget->renderProductItem($product);
    }

    /**
     * @return array
     */
    protected function loadProducts(): array
    {
        return $this->productWidget->loadProducts($this, 'repeatable_product_slider_items');
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
                'params'  => $this->getData(),
                'items'   => $this->loadProducts(),
            ]
        )->toHtml();
    }
}
