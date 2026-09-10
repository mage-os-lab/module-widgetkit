<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use MageOS\Widgetkit\Model\PreviewThemeResolver;
use MageOS\Widgetkit\Model\PreviewStylesheet;

final class PreviewTest extends TestCase
{
    public function testComposerThemeAssetUsesMagentoFallback(): void
    {
        $resolver = $this->createStub(PreviewThemeResolver::class);
        $resolver->method('getThemePath')->willReturn('Test/child');
        $directory = $this->createStub(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $tmp = sys_get_temp_dir() . '/widgetkit-unit-' . uniqid();
        mkdir($tmp);
        file_put_contents($tmp . '/parent.css', '@layer utilities{.layered{color:blue}}');
        $directory->method('getRoot')->willReturn($tmp);
        $directory->method('getPath')->willReturn($tmp);
        $url = $this->createStub(\Magento\Framework\UrlInterface::class);
        $url->method('getBaseUrl')->willReturn('https://example.test/media/');
        $locale = $this->createStub(\Magento\Framework\Locale\ResolverInterface::class);
        $locale->method('getLocale')->willReturn('en_US');
        $asset = $this->createStub(\Magento\Framework\View\Asset\File::class);
        $asset->method('getSourceFile')->willReturn($tmp . '/parent.css');
        $asset->method('getUrl')->willReturn('https://example.test/static/frontend/Test/child/en_US/css/styles.css');
        $assets = $this->createStub(\Magento\Framework\View\Asset\Repository::class);
        $assets->method('createAsset')->willReturn($asset);
        $sut = new PreviewStylesheet($resolver, $directory, $url, $locale, new \Psr\Log\NullLogger(), $assets, new \MageOS\Widgetkit\Model\PreviewCss());
        self::assertNotNull($sut->getUrl());
        foreach (glob($tmp . '/mageos_widgetkit/preview/*.css') as $file) { unlink($file); }
        rmdir($tmp . '/mageos_widgetkit/preview'); rmdir($tmp . '/mageos_widgetkit');
        unlink($tmp . '/parent.css'); rmdir($tmp);
    }
}
