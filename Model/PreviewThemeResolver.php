<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model;

use MageOS\Widgetkit\Helper\Config;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Which theme's compiled CSS should style widget previews in the PageBuilder stage for
 * the CMS page/block currently open in the admin editor:
 *  - the theme assigned to its first specifically-assigned store view, when that theme is
 *    a Hyvä child ([[HyvaThemeChecker]]);
 *  - otherwise (no store view assigned - a new entity or "All Store Views" - or that
 *    store view's theme isn't a Hyvä child) the admin-configured default preview theme
 *    (mageos_widgetkit/preview/theme).
 */
class PreviewThemeResolver
{
    private const XML_PATH_DESIGN_THEME = 'design/theme/theme_id';

    public function __construct(
        protected Config $config,
        protected RequestInterface $request,
        protected PageFactory $pageFactory,
        protected BlockFactory $blockFactory,
        protected ScopeConfigInterface $scopeConfig,
        protected ThemeProviderInterface $themeProvider,
        protected HyvaThemeChecker $hyvaThemeChecker
    ) {
    }

    /**
     * @return string|null
     */
    public function getThemePath(): ?string
    {
        $storeId = $this->getFirstAssignedStoreId();
        $themePath = $storeId !== null ? $this->getHyvaChildThemePathForStore($storeId) : null;

        return $themePath ?? $this->config->getPreviewThemePath();
    }

    /**
     * First store view specifically assigned to the CMS page/block currently open in the
     * editor, or null when it targets no particular store view (a not-yet-saved entity,
     * or one explicitly assigned to "All Store Views").
     *
     * @return int|null
     */
    private function getFirstAssignedStoreId(): ?int
    {
        $pageId = $this->request->getParam('page_id');
        $blockId = $this->request->getParam('block_id');

        if ($pageId) {
            $entity = $this->pageFactory->create();
        } elseif ($blockId) {
            $entity = $this->blockFactory->create();
        } else {
            return null;
        }

        $entity->load($pageId ?: $blockId);
        if (!$entity->getId()) {
            return null;
        }

        foreach ((array)$entity->getStores() as $storeId) {
            if ((int)$storeId !== 0) {
                return (int)$storeId;
            }
        }

        return null;
    }

    /**
     * @param int $storeId
     * @return string|null
     */
    private function getHyvaChildThemePathForStore(int $storeId): ?string
    {
        $themeId = $this->scopeConfig->getValue(self::XML_PATH_DESIGN_THEME, ScopeInterface::SCOPE_STORE, $storeId);
        if (!$themeId) {
            return null;
        }

        $theme = $this->themeProvider->getThemeById($themeId);
        if (!$theme || !$theme->getId() || !$this->hyvaThemeChecker->isChild($theme)) {
            return null;
        }

        return $theme->getThemePath();
    }
}
