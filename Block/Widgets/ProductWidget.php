<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Review\Model\Review;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Review\Model\AppendSummaryDataFactory;

class ProductWidget extends Template implements BlockInterface
{
    protected ?LayoutInterface $productItemLayout = null;

    public function __construct(
        protected State $state,
        protected Conditions $conditionsHelper,
        protected CollectionFactory $productCollectionFactory,
        protected LayoutFactory $layoutFactory,
        protected ReviewRenderer $reviewRenderer,
        protected AppendSummaryDataFactory $appendSummaryDataFactory,
        Context $context,
        protected bool $isHyvaWidget = true,
        protected string $_adminhtmlCatalogListItemHandle = 'hyva_catalog_list_item',
        protected string $_frontendCatalogListItemHandle = 'catalog_list_item',
        protected string $_frontendBlockItemName = 'product_list_item',
        protected string $_reviewSummaryTemplate = 'MageOS_Widgetkit::product/hyva/list/review-summary.phtml',
        array $data = []
    ) {
        parent::__construct($context, $data);
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
                $layout->getUpdate()->addHandle($this->_adminhtmlCatalogListItemHandle);
            } else {
                $layout->getUpdate()->addHandle($this->_frontendCatalogListItemHandle);
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
     * @throws NoSuchEntityException
     */
    public function renderProductItem(Product $product): string
    {
        $listItemBlock = $this->getProductItemLayout()->getBlock($this->_frontendBlockItemName);
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

        if ($this->state->getAreaCode() !== \Magento\Framework\App\Area::AREA_ADMINHTML && $this->isHyvaWidget) {
            $compareJSBlock = $this->getProductItemLayout()->getBlock('category.products.list.js.compare');
            $listItemBlockHtml .= $compareJSBlock->toHtml();
            $wishlistJSBlock = $this->getProductItemLayout()->getBlock('category.products.list.js.wishlist');
            $listItemBlockHtml .= $wishlistJSBlock->toHtml();
        }

        return $listItemBlockHtml;
    }

    /**
     * @param Template $block
     * @param string $repeatableFieldKey
     * @return array
     */
    public function loadProducts(Template $block, string $repeatableFieldKey): array
    {
        $rawItems = $block->getRepeatableField($repeatableFieldKey);
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

        $products = [];
        foreach ($rawItems as $rawItem) {
            if (empty($rawItem['product'])) {
                continue;
            }
            $product = $productsById[(int)$rawItem['product']] ?? null;
            unset($rawItem['product']);
            if ($product) {
                foreach ($rawItem as $key => $data) {
                    $product->setData($key, $data);
                }
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * @param Product $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getReviewSummaryHtml(Product $product): string
    {
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
