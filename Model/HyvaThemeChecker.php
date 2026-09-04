<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Whether a frontend theme descends from one of Hyvä's own base themes, as opposed to
 * an unrelated theme family (Magento's Luma/Blank, a third-party theme, etc.) that
 * happens to be installed and registered alongside it.
 */
class HyvaThemeChecker
{
    private const HYVA_ROOT_THEME_PATHS = ['Hyva/default', 'Hyva/default-csp'];

    /**
     * @param ThemeInterface $theme
     * @return bool
     */
    public function isChild(ThemeInterface $theme): bool
    {
        foreach ($theme->getInheritedThemes() as $ancestor) {
            if ($ancestor->getId() === $theme->getId()) {
                continue;
            }

            if (in_array($ancestor->getThemePath(), self::HYVA_ROOT_THEME_PATHS, true)) {
                return true;
            }
        }

        return false;
    }
}
