<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use MageOS\Widgetkit\Model\PreviewEnvironment;
use MageOS\Widgetkit\Model\PreviewThemeResolver;

final class PreviewEnvironmentTest extends TestCase
{
    public function testFailureRestoresEmulationAndNestedRenderDoesNotRestartIt(): void
    {
        $emulation = $this->createMock(\Magento\Store\Model\App\Emulation::class);
        $emulation->expects(self::once())->method('startEnvironmentEmulation')->with(2, 'frontend', true);
        $emulation->expects(self::once())->method('stopEnvironmentEmulation');
        $resolver = $this->createStub(PreviewThemeResolver::class);
        $resolver->method('getStoreId')->willReturn(2);
        $resolver->method('getThemePath')->willReturn('Test/child');
        $design = $this->createMock(\Magento\Framework\View\DesignInterface::class);
        $design->expects(self::once())->method('setDesignTheme')->with('Test/child', 'frontend');
        $sut = new PreviewEnvironment($emulation, $resolver, $design);
        try {
            $sut->render(function () use ($sut) {
                return $sut->render(function () { throw new RuntimeException('render failed'); });
            });
            self::fail('Rendering must propagate the failure.');
        } catch (RuntimeException $e) { self::assertSame('render failed', $e->getMessage()); }
        self::assertFalse($sut->isActive());
    }
    public function testExplicitEditorStoreAndDefaultStoreAreResolved(): void
    {
        $request = $this->createStub(\Magento\Framework\App\RequestInterface::class);
        $request->method('getParam')->willReturnMap([['widgetkit_store_id', null, 2]]);
        $store = $this->createStub(\Magento\Store\Model\Store::class);
        $store->method('getId')->willReturn(2);
        $store->method('isActive')->willReturn(true);
        $manager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $manager->expects(self::once())->method('getStore')->with(2)->willReturn($store);
        $resolver = new PreviewThemeResolver(
            $this->createStub(\MageOS\Widgetkit\Helper\Config::class), $request,
            $this->createStub(\Magento\Cms\Model\PageFactory::class),
            $this->createStub(\Magento\Cms\Model\BlockFactory::class),
            $this->createStub(\Magento\Framework\App\Config\ScopeConfigInterface::class),
            $this->createStub(\Magento\Framework\View\Design\Theme\ThemeProviderInterface::class),
            new \MageOS\Widgetkit\Model\HyvaThemeChecker(), $manager
        );
        self::assertSame(2, $resolver->getStoreId());
    }
}
