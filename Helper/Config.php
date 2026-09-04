<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    private const XML_PATH_PREVIEW_THEME = 'mageos_widgetkit/preview/theme';

    protected $scopeConfig;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
    }

    /**
     * Theme path whose compiled Tailwind CSS should style widget previews
     * in the PageBuilder stage. Empty/null means: render widget previews unstyled.
     *
     * @param int|string|null $storeId
     * @return string|null
     */
    public function getPreviewThemePath($storeId = null): ?string
    {
        $themePath = $this->scopeConfig->getValue(
            self::XML_PATH_PREVIEW_THEME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $themePath ?: null;
    }
}
