<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Locale\ResolverInterface as LocaleResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Psr\Log\LoggerInterface;

class PreviewStylesheet
{
    private const CACHE_SUBDIR = 'mageos_widgetkit/preview';

    public function __construct(
        protected PreviewThemeResolver $previewThemeResolver,
        protected DirectoryList $directoryList,
        protected UrlInterface $urlBuilder,
        protected LocaleResolverInterface $localeResolver,
        protected LoggerInterface $logger,
        protected Repository $assets,
        protected PreviewCss $css
    ) {
    }

    public function getScopeId(): string
    {
        return substr(hash('sha256', ($this->previewThemeResolver->getThemePath() ?? '')
            . ':' . $this->localeResolver->getLocale()), 0, 20);
    }

    public function getUrl(): ?string
    {
        $theme = $this->previewThemeResolver->getThemePath();
        if (!$theme) { return null; }
        $temporary = null;
        try {
            // Magento resolves registered Composer themes and inherited parent assets.
            $asset = $this->assets->createAsset('css/styles.css', [
                'area' => 'frontend', 'theme' => $theme, 'locale' => $this->localeResolver->getLocale()
            ]);
            $source = $asset->getSourceFile();
            if (!$source || !is_readable($source)) { throw new \RuntimeException('No compiled css/styles.css found.'); }
            $contents = file_get_contents($source);
            $assetBase = substr($asset->getUrl(), 0, strrpos($asset->getUrl(), '/') + 1);
            $key = hash('sha256', $contents . $assetBase . $this->getScopeId() . $this->css->getCacheKey());
            $directory = $this->directoryList->getPath(DirectoryList::MEDIA) . '/' . self::CACHE_SUBDIR;
            $target = $directory . '/' . $key . '.css';
            if (!is_file($target)) {
                $contents = preg_replace_callback('/url\(\s*([\'"]?)([^\'")]+)\1\s*\)/i',
                    static function (array $m) use ($assetBase): string {
                        $path = trim($m[2]);
                        if (preg_match('~^(?:[a-z][a-z\d+.-]*:|/|#)~i', $path)) { return $m[0]; }
                        return 'url(' . $m[1] . $assetBase . $path . $m[1] . ')';
                    }, $contents);
                $contents = $this->css->transform($contents, '[data-widgetkit-preview="' . $this->getScopeId() . '"]');
                if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new \RuntimeException('Could not create preview cache directory.');
                }
                $temporary = tempnam($directory, 'preview-');
                if ($temporary === false || file_put_contents($temporary, $contents) === false || !rename($temporary, $target)) {
                    throw new \RuntimeException('Could not write preview stylesheet.');
                }
                $temporary = null;
                chmod($target, 0644);
            }
            return rtrim($this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]), '/')
                . '/' . self::CACHE_SUBDIR . '/' . $key . '.css';
        } catch (\Throwable $e) {
            $this->logger->warning('MageOS_Widgetkit preview CSS: ' . $e->getMessage());
            return null;
        } finally {
            if ($temporary && is_file($temporary)) { unlink($temporary); }
        }
    }
}
