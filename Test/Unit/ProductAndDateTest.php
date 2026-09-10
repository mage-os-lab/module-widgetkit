<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Product;
use MageOS\Widgetkit\Block\Widgets\ProductWidget;
use MageOS\Widgetkit\Block\Widgets\ProductGrid;
use MageOS\Widgetkit\Block\Widgets\ProductSlider;
use MageOS\Widgetkit\Block\Widgets\Countdown;

final class ProductAndDateTest extends TestCase
{
    public function testCalendarOverflowIsRejected(): void
    {
        $block = (new ReflectionClass(Countdown::class))->newInstanceWithoutConstructor();
        $timezone = $this->createStub(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $timezone->method('getConfigTimezone')->willReturn('UTC');
        (new ReflectionProperty($block, 'timezone'))->setValue($block, $timezone);
        foreach (['02/31/2027 12:00', '02/29/2027 12:00', '04/31/2027 12:00'] as $date) {
            $block->setData('date', $date);
            self::assertNull($block->getTimestamp('date'), $date);
        }
        $block->setData('date', '02/29/2028 12:00');
        self::assertSame('2028-02-29 12:00:00', gmdate('Y-m-d H:i:s', $block->getTimestamp('date')));
    }
    public function testParentWidgetsTagEverySelectedProductIncludingFilteredProducts(): void
    {
        foreach ([ProductGrid::class => 'repeatable_product_grid_items', ProductSlider::class => 'repeatable_product_slider_items'] as $class => $field) {
            $widget = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $widget->setData($field, [['product' => '1'], ['product' => '94'], ['product' => '1']]);
            self::assertInstanceOf(\Magento\Framework\DataObject\IdentityInterface::class, $widget);
            self::assertSame(['cat_p_1', 'cat_p_94'], $widget->getIdentities());
        }
    }
    public function testRepeatedProductsKeepIndependentPlacementAndCannotOverrideCatalogData(): void
    {
        [$renderer, $filters] = $this->renderer();
        $rows = [['product' => '1', 'col_start_global' => 'col-start-1', 'name' => 'OVERRIDE'],
            ['product' => '1', 'col_start_global' => 'col-start-2', 'price' => '0']];
        $items = $renderer->loadProducts(new TemplateHarness(['rows' => $rows]), 'rows');
        self::assertCount(2, $items);
        self::assertNotSame($items[0], $items[1]);
        self::assertSame('col-start-1', $items[0]->getData('col_start_global'));
        self::assertSame('col-start-2', $items[1]->getData('col_start_global'));
        self::assertSame('Catalog name', $items[0]->getName());
        self::assertEquals(10, $items[1]->getData('price'));
    }
    public function testProductCollectionRequiresEnabledAndIndividuallyVisibleProducts(): void
    {
        [$renderer, $filters] = $this->renderer();
        $renderer->loadProducts(new TemplateHarness(['rows' => [['product' => '1']]]), 'rows');
        self::assertSame(1, $filters->values['status'] ?? null);
        self::assertSame(['in' => [2, 3, 4]], $filters->values['visibility'] ?? null);
        self::assertSame(2, $filters->store);
    }
    private function renderer(): array
    {
        $filters = (object)['values' => [], 'store' => null];
        $product = (new ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $product->setData(['entity_id' => 1, 'name' => 'Catalog name', 'price' => 10]);
        $collection = $this->createStub(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        foreach (['addMinimalPrice', 'addFinalPrice', 'addTaxPercents', 'addAttributeToSelect', 'addUrlRewrite', 'addStoreFilter'] as $method) {
            $collection->method($method)->willReturnSelf();
        }
        $collection->method('setStoreId')->willReturnCallback(function ($id) use ($filters, $collection) { $filters->store = $id; return $collection; });
        $collection->method('addAttributeToFilter')->willReturnCallback(function ($attr, $value) use ($filters, $collection) {
            $filters->values[$attr] = $value; return $collection;
        });
        $collection->method('getIterator')->willReturn(new ArrayIterator([$product]));
        $factory = $this->createStub(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $factory->method('create')->willReturn($collection);
        $store = $this->createStub(\Magento\Store\Model\Store::class);
        $store->method('getId')->willReturn(2);
        $sm = $this->createStub(\Magento\Store\Model\StoreManagerInterface::class);
        $sm->method('getStore')->willReturn($store);
        $renderer = (new ReflectionClass(ProductWidget::class))->newInstanceWithoutConstructor();
        foreach (['productCollectionFactory' => $factory, '_storeManager' => $sm] as $key => $value) {
            (new ReflectionProperty($renderer, $key))->setValue($renderer, $value);
        }
        return [$renderer, $filters];
    }
}
