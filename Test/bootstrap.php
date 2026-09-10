<?php
declare(strict_types=1);
$magento = getenv('MAGENTO_ROOT');
if (!$magento || !is_file($magento . '/vendor/autoload.php')) {
    throw new RuntimeException('Set MAGENTO_ROOT to an installed Magento/Hyva project.');
}
$loader = require $magento . '/vendor/autoload.php';
$loader->addPsr4('MageOS\\Widgetkit\\', dirname(__DIR__), true);

// Use Magento's unit-test factory generator, without depending on or changing the
// installed project's generated classes. A fresh DI compilation can remove them.
$generatedDirectory = sys_get_temp_dir() . '/widgetkit-test-generated-' . bin2hex(random_bytes(8));
$generatedLoader = new \Magento\Framework\TestFramework\Unit\Autoloader\GeneratedClassesAutoloader(
    [new \Magento\Framework\TestFramework\Unit\Autoloader\FactoryGenerator()],
    new \Magento\Framework\Code\Generator\Io(new \Magento\Framework\Filesystem\Driver\File(), $generatedDirectory)
);
spl_autoload_register([$generatedLoader, 'load']);
register_shutdown_function(static function () use ($generatedDirectory): void {
    if (!is_dir($generatedDirectory)) { return; }
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($generatedDirectory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($generatedDirectory);
});
require __DIR__ . '/Support/TemplateHarness.php';
