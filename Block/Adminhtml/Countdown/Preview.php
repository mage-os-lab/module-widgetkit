<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Adminhtml\Countdown;

use Magento\Widget\Block\BlockInterface;
use MageOS\Widgetkit\Block\Widgets\Countdown;

class Preview extends Countdown implements BlockInterface
{
    public function getCacheKeyInfo(): array
    {
        return [];
    }

    public function getCacheKey(): string
    {
        return '';
    }

    protected function _loadCache(): string
    {
        if ($this->hasData('translate_inline')) {
            $this->inlineTranslation->suspend($this->getData('translate_inline'));
        }
        $this->_beforeToHtml();
        return $this->_toHtml();
    }
}
