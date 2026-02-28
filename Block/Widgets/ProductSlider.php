<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Review\Model\Review;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Review\Model\AppendSummaryDataFactory;

class ProductSlider extends HyvaWidget implements BlockInterface
{
    private ?LayoutInterface $productItemLayout = null;

    private ?LayoutInterface $formkeyLayout = null;

    public function __construct(
        protected State $state,
        protected Conditions $conditionsHelper,
        protected CollectionFactory $productCollectionFactory,
        protected LayoutFactory $layoutFactory,
        protected ReviewRenderer $reviewRenderer,
        protected AppendSummaryDataFactory $appendSummaryDataFactory,
        Context $context,
        protected string $_mainTemplate = 'MageOS_Widgetkit::widget/hyva/product-slider/templates/template.phtml',
        protected string $_navTemplate = 'MageOS_Widgetkit::widget/hyva/product-slider/templates/template-nav.phtml',
        protected string $_slidenavTemplate = 'MageOS_Widgetkit::widget/hyva/product-slider/templates/template-slidenav.phtml',
        protected string $_itemsTemplate = 'MageOS_Widgetkit::widget/hyva/product-slider/templates/template-items.phtml',
        protected string $_reviewSummaryTemplate = 'MageOS_Widgetkit::product/hyva/list/review-summary.phtml',
        array $data = []
    ) {
        parent::__construct($conditionsHelper, $context, $data);
    }

    /**
     * @return LayoutInterface
     * @throws LocalizedException
     */
    public function getProductItemLayout(): LayoutInterface
    {
        if ($this->productItemLayout === null) {
            $layout = $this->layoutFactory->create();
            if ($this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
                $layout->getUpdate()->addHandle('hyva_catalog_list_item');
            } else {
                $layout->getUpdate()->addHandle('catalog_list_item');
            }
            $layout->getUpdate()->load();
            $layout->generateXml();
            $layout->generateElements();
            $this->productItemLayout = $layout;
        }
        return $this->productItemLayout;
    }

    /**
     * @param Product $product
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function renderProductItem(Product $product): string
    {
        $listItemBlock = $this->getProductItemLayout()->getBlock('product_list_item');
        if (!$listItemBlock) {
            return '';
        }
        if ($this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $listItemBlock->setData('review_summary_block_html', $this->getReviewSummaryHtml($product));
        } else {
            if ($product->getRatingSummary() === null) {
                $this->appendSummaryDataFactory->create()
                    ->execute(
                        $product,
                        (int)$this->_storeManager->getStore()->getId(),
                        Review::ENTITY_PRODUCT_CODE
                    );
                if ($product->getRatingSummary() === null) {
                    $product->setRatingSummary(0);
                }
            }
            $formKeyBlock = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\FormKey::class
            );
            $this->getProductItemLayout()->setBlock('formkey', $formKeyBlock);
        }
        $listItemBlock->setData('image_display_area', 'category_page_grid');
        $listItemBlock->setData('view_mode', 'grid');
        $listItemBlock->setData('product', $product);

        $listItemBlockHtml = $listItemBlock->toHtml();

        if ($this->state->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $compareJSBlock = $this->getProductItemLayout()->getBlock('category.products.list.js.compare');
            $listItemBlockHtml .= $compareJSBlock->toHtml();
            $wishlistJSBlock = $this->getProductItemLayout()->getBlock('category.products.list.js.wishlist');
            $listItemBlockHtml .= $wishlistJSBlock->toHtml();
        }

        return $listItemBlockHtml;
    }

    /**
     * @return array
     */
    protected function loadProducts(): array
    {
        $rawItems = $this->getRepeatableField('repeatable_product_slider_items');
        if (empty($rawItems)) {
            return [];
        }

        $productIds = array_values(array_filter(array_map(
            static fn($row) => !empty($row['product']) ? (int)$row['product'] : null,
            $rawItems
        )));

        if (empty($productIds)) {
            return [];
        }

        $collection = $this->productCollectionFactory->create();
        $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect([
                'name', 'sku', 'status', 'visibility',
                'image', 'small_image', 'thumbnail',
                'price', 'special_price', 'special_from_date', 'special_to_date',
                'short_description',
            ])
            ->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addUrlRewrite();

        $productsById = [];
        foreach ($collection as $product) {
            $productsById[$product->getId()] = $product;
        }

        // Preserve the repeatable-field order
        $products = [];
        foreach ($rawItems as $rawItem) {
            if (empty($rawItem['product'])) {
                continue;
            }
            $product = $productsById[(int)$rawItem['product']] ?? null;
            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function renderMainTemplate(): string
    {
        return $this->getLayout()->createBlock(
            self::class
        )->setTemplate(
            $this->_mainTemplate
        )->setData(
            [
                'params'  => $this->getData(),
                'items'   => $this->loadProducts(),
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
            self::class
        )->setTemplate(
            $this->_navTemplate
        )->setData(
            [
                'params' => $this->getData('params'),
                'items'  => $this->getData('items'),
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
            self::class
        )->setTemplate(
            $this->_slidenavTemplate
        )->setData(
            [
                'params' => $this->getData('params'),
                'items'  => $this->getData('items'),
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
            self::class
        )->setTemplate(
            $this->_itemsTemplate
        )->setData(
            [
                'params'   => $this->getData('params'),
                'settings' => $itemsSettings,
                'items'    => $this->getData('items'),
            ]
        )->toHtml();
    }

    /**
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getReviewSummaryHtml(Product $product) {
        if ($product->getRatingSummary() === null) {
            $this->appendSummaryDataFactory->create()
                ->execute(
                    $product,
                    (int)$this->_storeManager->getStore()->getId(),
                    Review::ENTITY_PRODUCT_CODE
                );
        }

        if (null === $product->getRatingSummary()) {
            $product->setRatingSummary(0);
        }
        $this->reviewRenderer->setTemplate($this->_reviewSummaryTemplate);
        $this->reviewRenderer->setDisplayIfEmpty(true);
        $this->reviewRenderer->setProduct($product);
        return $this->reviewRenderer->toHtml();
    }
}
