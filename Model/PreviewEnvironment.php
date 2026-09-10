<?php
declare(strict_types=1);
namespace MageOS\Widgetkit\Model;

use Magento\Framework\App\Area;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\App\Emulation;

class PreviewEnvironment
{
    private bool $active = false;

    public function __construct(
        private Emulation $emulation,
        private PreviewThemeResolver $resolver,
        private DesignInterface $design
    ) {
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function render(callable $render): string
    {
        if ($this->active) { return $render(); }
        $store = $this->resolver->getStoreId();
        $theme = $this->resolver->getThemePath();
        $this->active = true;
        try {
            $this->emulation->startEnvironmentEmulation($store, Area::AREA_FRONTEND, true);
            if ($theme) { $this->design->setDesignTheme($theme, Area::AREA_FRONTEND); }
            return $render();
        } finally {
            try { $this->emulation->stopEnvironmentEmulation(); }
            finally { $this->active = false; }
        }
    }
}
