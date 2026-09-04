<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Locale\ResolverInterface as LocaleResolverInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\Parser as CssParser;
use Sabberworm\CSS\Property\Selector;
use Sabberworm\CSS\RuleSet\DeclarationBlock;

/**
 * Serves widget previews a real, theme-accurate stylesheet: takes the configured theme's
 * already-compiled Tailwind CSS (web/css/styles.css) and rewrites it so it responds to
 * PageBuilder's simulated viewport toggle (a CSS class, not a real media query) instead
 * of @media queries.
 */
class PreviewStylesheet
{
    private const SELECTOR_DEFAULT = '.pagebuilder-stage-wrapper '
        . '.pagebuilder-content-type-wrapper > .pagebuilder-content-type > [data-content-type="widget"]';

    private const SELECTOR_MOBILE = '.pagebuilder-stage-wrapper.mobile-viewport '
        . '.pagebuilder-content-type-wrapper > .pagebuilder-content-type > [data-content-type="widget"]';

    private const CACHE_SUBDIR = 'mageos_widgetkit/preview';

    public function __construct(
        protected PreviewThemeResolver $previewThemeResolver,
        protected DirectoryList $directoryList,
        protected UrlInterface $urlBuilder,
        protected LocaleResolverInterface $localeResolver,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Public URL of the theme-accurate preview stylesheet, or null when no preview theme
     * is configured, or the configured theme has no compiled CSS to read.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        $themePath = $this->previewThemeResolver->getThemePath();
        if ($themePath === null) {
            return null;
        }

        $sourceFile = $this->getSourceCssPath($themePath);
        if (!is_file($sourceFile)) {
            $this->logger->warning(
                "MageOS_Widgetkit preview: theme \"{$themePath}\" has no compiled CSS at "
                . "{$sourceFile} - run \"npm run build\" in its web/tailwind directory. "
                . "Widget previews will render unstyled until then."
            );
            return null;
        }

        $cacheFile = $this->getCacheFilePath($themePath);
        if (!is_file($cacheFile) || filemtime($cacheFile) < filemtime($sourceFile)) {
            $this->regenerate($sourceFile, $cacheFile, $themePath);
        }

        $relativePath = self::CACHE_SUBDIR . '/' . basename($cacheFile);

        return rtrim($this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]), '/')
            . '/' . $relativePath
            . '?v=' . filemtime($cacheFile);
    }

    /**
     * @param string $themePath
     * @return string
     */
    private function getSourceCssPath(string $themePath): string
    {
        return $this->directoryList->getRoot() . '/app/design/frontend/' . $themePath . '/web/css/styles.css';
    }

    /**
     * The source CSS lives at app/design/frontend/{theme}/web/css/styles.css and its
     * url(../fonts/...)-style references are relative to that directory. We're about to
     * serve a rewritten copy from a completely different location (pub/media/...), so
     * those relative references need rewriting to the theme's real static asset URL or
     * fonts/background images 404.
     *
     * @param string $css
     * @param string $themePath
     * @return string
     */
    private function rewriteRelativeUrls(string $css, string $themePath): string
    {
        // Deployed static paths drop the theme source's "web/" prefix, e.g.
        // app/design/frontend/{theme}/web/css/styles.css is served from
        // pub/static/frontend/{theme}/{locale}/css/styles.css.
        $staticBase = rtrim($this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_STATIC]), '/')
            . '/frontend/' . $themePath . '/' . $this->localeResolver->getLocale() . '/css/';

        return preg_replace_callback(
            '/url\(\s*([\'"]?)(?!(?:data:|https?:|\/))([^\'")]+)\1\s*\)/i',
            fn (array $m) => 'url(' . $m[1] . $staticBase . $m[2] . $m[1] . ')',
            $css
        );
    }

    /**
     * Pull every @font-face { ... } block out of the raw CSS text (bodies never contain
     * nested braces, so a non-greedy match is safe) and hand them back separately from the
     * rest of the stylesheet with that text removed, working around a sabberworm parser gap.
     *
     * @param string $css
     * @return array{0: string, 1: string} [$cssWithFontFacesRemoved, $extractedFontFaceBlocks]
     */
    private function extractFontFaceBlocks(string $css): array
    {
        $extracted = '';
        $css = preg_replace_callback(
            '/@font-face\s*\{[^{}]*\}/i',
            function (array $m) use (&$extracted) {
                $extracted .= $m[0];
                return '';
            },
            $css
        );

        return [$css, $extracted];
    }

    /**
     * Pull `:root,:host{ ... }` (Tailwind v4's @theme custom property definitions - --spacing,
     * --color-*, --breakpoint-*, --form-*, everything else utility rules reference via var())
     * and `html,:host{ ... }` (base text/font preflight) out of the raw CSS text before parsing.
     * Sabberworm mis-associates the :root,:host block's selector list with unrelated adjacent
     * rules (observed merging in `.flow-root` and a `:where(:root:has(...))` selector from
     * neighboring rules), so re-scoping it based on Sabberworm's own parsed selectors is
     * unreliable - and even parsed correctly, :root/:host/html can only ever be the actual
     * document root/shadow host, never a descendant of our wrapper selector, so prefixing them
     * would make the whole block permanently dead. Custom properties inherit normally regardless
     * of where they're declared, so this can just stay verbatim.
     *
     * @param string $css
     * @return array{0: string, 1: string} [$cssWithRootHostRemoved, $extractedRootHostBlock]
     */
    private function extractRootHostBlock(string $css): array
    {
        $extracted = '';
        $css = preg_replace_callback(
            '/(?::root|html)\s*,\s*:host\s*\{[^{}]*\}/i',
            function (array $m) use (&$extracted) {
                $extracted .= $m[0];
                return '';
            },
            $css
        );

        return [$css, $extracted];
    }

    /**
     * @param string $themePath
     * @return string
     */
    private function getCacheFilePath(string $themePath): string
    {
        $slug = strtolower(str_replace(['/', '\\', ' '], '-', $themePath));
        $dir = $this->directoryList->getPath(DirectoryList::MEDIA) . '/' . self::CACHE_SUBDIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $slug . '.css';
    }

    /**
     * @param string $sourceFile
     * @param string $cacheFile
     * @param string $themePath
     * @return void
     */
    private function regenerate(string $sourceFile, string $cacheFile, string $themePath): void
    {
        $css = $this->rewriteRelativeUrls(file_get_contents($sourceFile), $themePath);
        [$css, $preserved] = $this->extractFontFaceBlocks($css);
        [$css, $rootHost] = $this->extractRootHostBlock($css);
        $preserved .= $rootHost;

        try {
            $document = (new CssParser($css))->parse();
        } catch (\Exception $e) {
            $this->logger->warning(
                "MageOS_Widgetkit preview: failed to parse {$sourceFile}: " . $e->getMessage()
            );
            file_put_contents($cacheFile, '');
            return;
        }

        $default = '';
        $mobile = '';

        foreach ($document->getContents() as $item) {
            if ($item instanceof DeclarationBlock) {
                if ($this->isDocumentLevelOnly($item)) {
                    $preserved .= (string)$item;
                    continue;
                }

                $default .= $this->rescope($item, self::SELECTOR_DEFAULT);
                $mobile .= $this->rescope($item, self::SELECTOR_MOBILE);
                continue;
            }

            if ($item instanceof AtRuleBlockList && strtolower($item->atRuleName()) === 'media') {
                $condition = strtolower($item->atRuleArgs());
                $hasMin = str_contains($condition, 'min-width');
                $hasMax = str_contains($condition, 'max-width');

                foreach ($item->getContents() as $inner) {
                    if (!$inner instanceof DeclarationBlock) {
                        continue;
                    }
                    if ($hasMax && !$hasMin) {
                        $mobile .= $this->rescope($inner, self::SELECTOR_MOBILE);
                    } else {
                        $default .= $this->rescope($inner, self::SELECTOR_DEFAULT);
                    }
                }
                continue;
            }

            if ($item instanceof AtRuleBlockList && strtolower($item->atRuleName()) === 'supports') {
                $innerDefault = '';
                $innerMobile = '';
                foreach ($item->getContents() as $inner) {
                    if (!$inner instanceof DeclarationBlock) {
                        continue;
                    }
                    $innerDefault .= $this->rescope($inner, self::SELECTOR_DEFAULT);
                    $innerMobile .= $this->rescope($inner, self::SELECTOR_MOBILE);
                }
                if ($innerDefault !== '') {
                    $default .= '@supports ' . $item->atRuleArgs() . " {{$innerDefault}}\n";
                    $mobile .= '@supports ' . $item->atRuleArgs() . " {{$innerMobile}}\n";
                }
                continue;
            }
            $preserved .= (string)$item;
        }

        file_put_contents($cacheFile, $preserved . "\n" . $default . "\n" . $mobile);
    }

    /**
     * True when every selector in the block is a document-level anchor (:root, :host) that
     * can only ever match the actual document root/shadow host - never a descendant of our
     * wrapper selector, so prefixing it would just make the whole rule permanently dead.
     *
     * @param DeclarationBlock $block
     * @return bool
     */
    private function isDocumentLevelOnly(DeclarationBlock $block): bool
    {
        $selectors = $block->getSelectors();
        if (!$selectors) {
            return false;
        }

        foreach ($selectors as $selector) {
            if (!in_array(strtolower(trim($selector->getSelector())), [':root', ':host'], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clone a declaration block with every selector prefixed by $wrapperSelector.
     *
     * @param DeclarationBlock $block
     * @param string $wrapperSelector
     * @return string
     */
    private function rescope(DeclarationBlock $block, string $wrapperSelector): string
    {
        $clone = clone $block;
        $selectors = [];
        foreach ($block->getSelectors() as $selector) {
            $selectors[] = new Selector($wrapperSelector . ' ' . $selector->getSelector());
        }
        $clone->setSelectors($selectors);

        return (string)$clone;
    }
}
