<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Model\Config\Source;

use MageOS\Widgetkit\Model\HyvaThemeChecker;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;

class PreviewTheme implements OptionSourceInterface
{
    public function __construct(
        protected CollectionFactory $themeCollectionFactory,
        protected HyvaThemeChecker $hyvaThemeChecker
    ) {
    }

    /**
     * Only Hyvä child themes are offered: the preview stylesheet is read from a theme's
     * compiled Tailwind CSS, which non-Hyvä themes (Luma, Blank, ...) don't produce.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('-- No preview styling --')]];

        $collection = $this->themeCollectionFactory->create()->addAreaFilter('frontend');
        foreach ($collection as $theme) {
            if (!$this->hyvaThemeChecker->isChild($theme)) {
                continue;
            }

            $options[] = [
                'value' => $theme->getThemePath(),
                'label' => $theme->getThemeTitle() . ' (' . $theme->getThemePath() . ')',
            ];
        }

        return $options;
    }
}
