<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\DesignInterface;
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
        protected Conditions $conditionsHelper,
        protected CollectionFactory $productCollectionFactory,
        protected LayoutFactory $layoutFactory,
        protected ReviewRenderer $reviewRenderer,
        protected AppendSummaryDataFactory $appendSummaryDataFactory,
        protected DesignInterface $viewDesign,
        protected State $state,
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
     * Whether the CURRENT (possibly emulated) design area is adminhtml.
     *
     * `\Magento\Framework\App\State::getAreaCode()` never changes during
     * `Magento\Store\Model\App\Emulation::startEnvironmentEmulation()` - only the
     * design/theme (`DesignInterface`) gets flipped to the emulated area. The Preview
     * blocks rely on that emulation to make the admin PageBuilder preview render
     * through the real frontend `catalog_list_item` handle (and therefore respect
     * frontend theme overrides of `Magento_Catalog::product/list/item.phtml`), so this
     * must check the emulation-aware design area, not the raw app state area code.
     *
     * @return bool
     */
    private function isAdminhtmlArea(): bool
    {
        return $this->viewDesign->getArea() === Area::AREA_ADMINHTML;
    }

    /**
     * Override the review-summary template. Lets modules extending this class swap the
     * template without duplicating the whole class.
     *
     * @param string $template
     * @return void
     */
    public function setReviewSummaryTemplate(string $template): void
    {
        $this->_reviewSummaryTemplate = $template;
    }

    /**
     * @return LayoutInterface
     * @throws LocalizedException
     */
    public function getProductItemLayout(): LayoutInterface
    {
        if ($this->productItemLayout === null) {
            $isAdminhtml = $this->isAdminhtmlArea();
            $handle = $isAdminhtml ? $this->_adminhtmlCatalogListItemHandle : $this->_frontendCatalogListItemHandle;

            // `Layout::getUpdate()` resolves which theme's layout XML to merge via
            // `Magento\Theme\Model\Theme\Resolver::get()`, whose own shortcut check compares
            // the CURRENT design theme's area against `\Magento\Framework\App\State::getAreaCode()`
            // (not `DesignInterface::getArea()`). `Store\Model\App\Emulation` never touches that
            // raw app state area code, so during an emulated admin preview the two disagree, the
            // resolver falls through to looking up the adminhtml area's theme regardless, and the
            // frontend `catalog_list_item` handle silently merges to nothing. Wrapping this in
            // `emulateAreaCode()` makes the app state area agree with the already-emulated design
            // area for the duration of the layout build, so the correct theme's layout XML (and
            // therefore its frontend theme overrides) actually gets merged.
            $this->productItemLayout = $this->state->emulateAreaCode(
                $isAdminhtml ? Area::AREA_ADMINHTML : Area::AREA_FRONTEND,
                function () use ($handle): LayoutInterface {
                    $layout = $this->layoutFactory->create();
                    $layout->getUpdate()->addHandle($handle);
                    $layout->getUpdate()->load();
                    $layout->generateXml();
                    $layout->generateElements();
                    return $layout;
                }
            );
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
        // Template FILE resolution (`Template::fetchView()`, triggered by `toHtml()` below) hits
        // the exact same `Theme\Resolver`/app-state-area mismatch as the layout XML merge in
        // `getProductItemLayout()` - so the whole render, not just the layout build, needs to run
        // with the app state area kept in sync with the (already emulation-aware) design area.
        // See the docblock on `isAdminhtmlArea()` for the full explanation.
        return $this->state->emulateAreaCode(
            $this->isAdminhtmlArea() ? Area::AREA_ADMINHTML : Area::AREA_FRONTEND,
            function () use ($product): string {
                $listItemBlock = $this->getProductItemLayout()->getBlock($this->_frontendBlockItemName);
                if (!$listItemBlock) {
                    return '';
                }
                if ($this->isAdminhtmlArea()) {
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

                if (!$this->isAdminhtmlArea() && $this->isHyvaWidget) {
                    $compareJSBlock = $this->getProductItemLayout()->getBlock('category.products.list.js.compare');
                    $listItemBlockHtml .= $compareJSBlock->toHtml();
                    $wishlistJSBlock = $this->getProductItemLayout()->getBlock('category.products.list.js.wishlist');
                    $listItemBlockHtml .= $wishlistJSBlock->toHtml();
                }

                return $listItemBlockHtml;
            }
        );
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
