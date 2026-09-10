<?php
declare(strict_types=1);

// Run against a local installed Magento project. No catalog, CMS, customer or order writes.
$root = getenv('MAGENTO_ROOT');
if (!$root) { throw new RuntimeException('Set MAGENTO_ROOT.'); }
chdir($root);
require 'app/bootstrap.php';
require dirname(__DIR__) . '/Support/TemplateHarness.php';
$om = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER)->getObjectManager();
$area = $argv[1] ?? 'frontend';
$storeId = (int)($argv[2] ?? 1);
$output = $argv[3] ?? sys_get_temp_dir() . '/widgetkit-smoke-' . uniqid();
if (!is_dir($output)) { mkdir($output, 0700, true); }
$om->get(\Magento\Framework\App\State::class)->setAreaCode($area);
$om->configure($om->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class)->load($area));
$stores = $om->get(\Magento\Store\Model\StoreManagerInterface::class);
$stores->setCurrentStore($area === 'frontend' ? $storeId : 0);
$om->get(\Magento\Framework\App\RequestInterface::class)->setParam('widgetkit_store_id', $storeId);
$design = $om->get(\Magento\Framework\View\DesignInterface::class);
$design->setArea($area)->setDesignTheme($design->getConfigurationDesignTheme($area, ['store' => $storeId]), $area);
$collection = $om->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)->create();
$collection->setStoreId($storeId)->addStoreFilter($storeId)->addAttributeToFilter('status', 1)
    ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])->setPageSize(1);
$productId = (int)$collection->getFirstItem()->getId();
if (!$productId) { throw new RuntimeException('The test store needs one visible enabled product.'); }
$layout = $om->get(\Magento\Framework\View\LayoutInterface::class);
$xml = simplexml_load_file(dirname(__DIR__, 2) . '/etc/widget.xml');
$results = [];
foreach ($xml->widget as $widget) {
    $params = TemplateHarness::fixture((string)$widget['id']);
    foreach ($params as $key => &$value) {
        if (str_starts_with($key, 'repeatable_')) {
            foreach ($value as &$row) {
                $row['product'] = (string)$productId;
                $row['col_start_desktop'] = 'lg:col-start-3';
            }
            unset($row);
        } elseif (str_ends_with($key, '_title')) { $value = 'Widgetkit runtime test'; }
    }
    unset($value);
    if ((string)$widget['id'] === 'mageos_countdown') { $params['mageos_countdown_end'] = '12/31/2027 23:59'; }
    foreach ($widget->parameters->parameter as $parameter) {
        if ((string)$parameter['name'] !== 'template') { continue; }
        foreach ($parameter->options->option as $option) {
            $template = (string)$option['value'];
            $renderClass = (string)$widget['class'];
            $renderTemplate = 'MageOS_Widgetkit::' . $template;
            if ($area === 'adminhtml') {
                $renderClass = (string)$widget->previewBlock;
                foreach ($widget->previewTemplates->previewTemplate as $preview) {
                    if ((string)$preview['name'] === $template) { $renderTemplate = (string)$preview; }
                }
            }
            $key = (string)$widget['id'] . ':' . basename($template);
            try {
                $block = $layout->createBlock($renderClass, '', ['data' => $params])->setTemplate($renderTemplate);
                $html = $block->toHtml();
                if ($html === '') { throw new RuntimeException('Empty output'); }
                if ($area === 'adminhtml' && !str_contains($html, 'data-widgetkit-preview=')) {
                    throw new RuntimeException('Preview environment plugin was not applied');
                }
                if ((string)$widget['id'] === 'mageos_product_grid' && !str_contains(html_entity_decode($html, ENT_QUOTES, 'UTF-8'), 'lg:col-start-3')) {
                    throw new RuntimeException('Product placement missing');
                }
                file_put_contents($output . '/' . basename($template) . '.html', $html);
                $results[$key] = ['pass' => true, 'bytes' => strlen($html)];
            } catch (Throwable $e) { $results[$key] = ['pass' => false, 'error' => get_class($e) . ': ' . $e->getMessage()]; }
        }
    }
}
if ($area === 'adminhtml') {
    $previousStore = $storeId === 1 ? 2 : 1;
    $stores->setCurrentStore($previousStore);
    $previousArea = $design->getArea();
    $preview = $layout->createBlock(\MageOS\Widgetkit\Block\Adminhtml\Banner\Preview::class);
    $preview->setData('mageos_banner_icon', 'widgetkit-test-nonexistent-icon');
    $preview->setTemplate('MageOS_Widgetkit::banner-full-background.phtml');
    $threw = false;
    try { $preview->toHtml(); } catch (Throwable $e) { $threw = true; }
    $results['exception_cleanup'] = ['pass' => $threw && (int)$stores->getStore()->getId() === $previousStore && $design->getArea() === $previousArea, 'exception_observed' => $threw, 'store_before' => $previousStore, 'area_after' => $design->getArea(), 'store_after' => (int)$stores->getStore()->getId()];
    $stores->setCurrentStore(0);
} else {
    $grid = $layout->createBlock(\MageOS\Widgetkit\Block\Widgets\ProductGrid::class);
    $grid->setData('repeatable_product_grid_items', [['product' => $productId], ['product' => 94]]);
    $tags = $grid->getIdentities();
    $results['cache_identities'] = ['pass' => in_array('cat_p_' . $productId, $tags, true) && in_array('cat_p_94', $tags, true), 'tags' => $tags];
}
$passed = count(array_filter($results, fn($row) => !$row['pass'])) === 0;
file_put_contents($output . '/results.json', json_encode(['passed' => $passed, 'results' => $results], JSON_PRETTY_PRINT));
echo json_encode(['area' => $area, 'store' => $storeId, 'passed' => $passed, 'results' => $results], JSON_PRETTY_PRINT) . PHP_EOL;
exit($passed ? 0 : 1);
